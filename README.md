# 🚀 Enterprise Project Management Dashboard

A full-stack IT project tracking system featuring **Role-Based Access Control (RBAC)**, workflow automation, and real-time visualization. Designed to streamline communication between System Admins, Managers, and Team Members.

## 🌟 Key Features

### 🔐 Role-Based Security
* **System Admin:** Full control over Users, Roles, and Projects. Can reset passwords, delete accounts/projects, and monitor all workflows.
* **Manager:** Can create projects, assign tasks to members, and **Approve/Reject** submitted work.
* **Member:** View assigned tasks, check deadlines, and upload proof of completion.

### ⚡ Core Functionality
* **Task Approval Workflow:** Members submit work -> Status changes to "For Review" -> Manager/Admin approves (Completed) or rejects (In Progress).
* **Search & Filtering:** Real-time search bars for Users, Projects, and Task History.
* **Visual Analytics:** Interactive Doughnut Chart (Chart.js) visualizing task distribution.
* **CRUD Operations:** Create, Read, Update, and Delete capabilities for Users and Projects.

## 🛠️ Tech Stack
* **Frontend:** HTML5, CSS3 (Split-screen auth, Responsive Sidebar), JavaScript (Fetch API, Chart.js).
* **Backend:** PHP (RESTful API architecture, Session Management).
* **Database:** MySQL (Relational tables with Foreign Keys).

## ⚙️ Installation & Setup

1.  **Clone the Repo:**
    ```bash
    git clone [https://github.com/YourUsername/IT-Project-Tracker.git](https://github.com/YourUsername/IT-Project-Tracker.git)
    ```
2.  **Database Configuration:**
    * Create a MySQL database named `project_tracker_db`.
    * Import the provided SQL schema (users, projects, tasks).
    * *Note: Ensure `projects` table has the `status` column.*
3.  **Configure Connection:**
    * Update `db_connect.php` with your local database credentials.
4.  **Run:**
    * Host on Apache (XAMPP/WAMP).
    * Default Admin Credentials: `admin` / `admin123`

## 📸 Screenshots
<img width="1903" height="1031" alt="image" src="https://github.com/user-attachments/assets/045a5c40-465f-43f4-aa50-23324b58b399" />
<img width="1873" height="1033" alt="image" src="https://github.com/user-attachments/assets/213193fc-b097-4477-892e-76638f06afe8" />


---
*Developed by Camille Precious D. Lalaguna 
