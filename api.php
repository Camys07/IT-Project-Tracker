<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

function sendError($msg) {
    echo json_encode(["status" => "error", "message" => $msg]);
    exit;
}

// --- 1. LOGIN ---
if ($action == 'login') {
    $user = $_POST['username'];
    $pass = md5($_POST['password']); 

    $stmt = $conn->prepare("SELECT id, role, full_name FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['full_name'];
        echo json_encode(["status" => "success", "role" => $row['role']]);
    } else {
        sendError("Invalid credentials");
    }
    exit;
}

if (!isset($_SESSION['user_id'])) sendError("Unauthorized");
$myRole = $_SESSION['role'];
$myId = $_SESSION['user_id'];

// --- 2. GET DASHBOARD DATA ---
if ($action == 'get_dashboard_data') {
    $data = [];
    $data['current_user'] = $_SESSION;

    // Projects
    $data['projects'] = $conn->query("SELECT * FROM projects ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

    // Users
    if (in_array($myRole, ['admin', 'manager'])) {
        $data['users'] = $conn->query("SELECT id, full_name, role FROM users")->fetch_all(MYSQLI_ASSOC);
    }

    // Tasks
    $sql = "SELECT t.*, u.full_name, p.title as project_title FROM tasks t 
            JOIN users u ON t.assigned_to = u.id 
            JOIN projects p ON t.project_id = p.id";
    
    if ($myRole == 'member') {
        $sql .= " WHERE t.assigned_to = $myId";
    }
    
    $data['tasks'] = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    // Stats
    $stats = ['Pending' => 0, 'In Progress' => 0, 'For Review' => 0, 'Completed' => 0];
    foreach ($data['tasks'] as $task) {
        if (isset($stats[$task['status']])) $stats[$task['status']]++;
    }
    $data['stats'] = $stats;

    echo json_encode($data);
    exit;
}

// --- 3. CORE ACTIONS (CREATE) ---

if ($action == 'add_project') {
    if (!in_array($myRole, ['admin', 'manager'])) sendError("Permission denied");
    $t = $_POST['title'];
    $conn->query("INSERT INTO projects (title) VALUES ('$t')");
    echo json_encode(["status" => "success"]);
    exit;
}

if ($action == 'assign_task') {
    if (!in_array($myRole, ['admin', 'manager'])) sendError("Permission denied");
    $pid = $_POST['project_id'];
    $uid = $_POST['user_id'];
    $title = $_POST['title'];
    $date = $_POST['deadline'];
    
    $stmt = $conn->prepare("INSERT INTO tasks (project_id, assigned_to, title, deadline) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $pid, $uid, $title, $date);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit;
}

if ($action == 'add_user') {
    if ($myRole != 'admin') sendError("Admins only");
    $u = $_POST['username'];
    $p = md5($_POST['password']);
    $n = $_POST['fullname'];
    $r = $_POST['role'];
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $u, $p, $n, $r);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit;
}

// --- 4. UPDATE ACTIONS ---

if ($action == 'update_role') {
    if ($myRole != 'admin') sendError("Admins only");
    $uid = $_POST['user_id'];
    $newRole = $_POST['new_role'];
    $conn->query("UPDATE users SET role='$newRole' WHERE id=$uid");
    echo json_encode(["status" => "success"]);
    exit;
}

if ($action == 'update_password') {
    if ($myRole != 'admin') sendError("Admins only");
    $uid = $_POST['user_id'];
    $newPass = md5($_POST['new_password']);
    $conn->query("UPDATE users SET password='$newPass' WHERE id=$uid");
    echo json_encode(["status" => "success"]);
    exit;
}

// NEW: Update Project Status
if ($action == 'update_project_status') {
    if ($myRole != 'admin') sendError("Admins only");
    $pid = $_POST['project_id'];
    $stat = $_POST['status'];
    $stmt = $conn->prepare("UPDATE projects SET status=? WHERE id=?");
    $stmt->bind_param("si", $stat, $pid);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit;
}

// --- 5. DELETE ACTIONS (NEW) ---

if ($action == 'delete_user') {
    if ($myRole != 'admin') sendError("Admins only");
    $id = $_POST['user_id'];
    // Prevent deleting yourself
    if ($id == $myId) sendError("Cannot delete yourself");
    
    $conn->query("DELETE FROM users WHERE id=$id");
    echo json_encode(["status" => "success"]);
    exit;
}

if ($action == 'delete_project') {
    if ($myRole != 'admin') sendError("Admins only");
    $id = $_POST['project_id'];
    $conn->query("DELETE FROM projects WHERE id=$id");
    echo json_encode(["status" => "success"]);
    exit;
}

// --- 6. TASK WORKFLOW ---

if ($action == 'submit_task') {
    $taskId = $_POST['task_id'];
    $fakeFile = "proof_doc_" . time() . ".pdf"; 
    $conn->query("UPDATE tasks SET status='For Review', proof_file='$fakeFile' WHERE id=$taskId");
    echo json_encode(["status" => "success"]);
    exit;
}

if ($action == 'review_task') {
    if (!in_array($myRole, ['admin', 'manager'])) sendError("Permission denied");
    $taskId = $_POST['task_id'];
    $decision = $_POST['decision'];
    $newStatus = ($decision === 'approve') ? 'Completed' : 'In Progress';
    $conn->query("UPDATE tasks SET status='$newStatus' WHERE id=$taskId");
    echo json_encode(["status" => "success"]);
    exit;
}
?>