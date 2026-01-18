<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

/* =======================
   KPI SUMMARY
======================= */
$totalApplications = $conn->query("SELECT COUNT(*) AS total FROM hostel_application")
    ->fetch_assoc()['total'] ?? 0;

$approvedApplications = $conn->query("SELECT COUNT(*) AS total FROM hostel_application WHERE status='Approved'")
    ->fetch_assoc()['total'] ?? 0;

$pendingApplications = $conn->query("SELECT COUNT(*) AS total FROM hostel_application WHERE status='Pending'")
    ->fetch_assoc()['total'] ?? 0;

$rejectedApplications = $conn->query("SELECT COUNT(*) AS total FROM hostel_application WHERE status='Rejected'")
    ->fetch_assoc()['total'] ?? 0;

$totalStudentsAssigned = $conn->query("
    SELECT COUNT(DISTINCT matrix_no) AS total 
    FROM student_room 
    WHERE checkout_date IS NULL
")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff Dashboard - e-Kolej UTeM</title>
  <link rel="stylesheet" href="style.css" />
   <style>
    .sidebar {
    width: 240px;
    background: #003366;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    }
    .container {
    display: flex;      
    min-height: 100vh;   
    }
    .dashboard-container {
    flex: 1;
    margin-left: 240px; 
    padding: 30px;
    background: #f8f8f8;
    }
 
    h2 { margin-bottom:5px; }
    .subtitle { color:#555; margin-bottom:25px; }
    .kpi-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap:20px;
      margin-bottom:30px;
    }
    .kpi-card {
      background:#fff;
      border-radius:12px;
      padding:20px;
      box-shadow:0 0 6px rgba(0,0,0,0.1);
    }

    .kpi-card h4 {
      margin:0;
      font-size:14px;
      color:#003366;
    }

    .kpi-card p {
      margin-top:10px;
      font-size:28px;
      font-weight:bold;
      color:#000;
    }
    
    .quick-links {
      background:#fff;
      padding:20px;
      border-radius:12px;
      box-shadow:0 0 6px rgba(0,0,0,0.1);
    }

    .quick-links h3 { margin-bottom:15px; color:#003366; }

    .quick-links a {
      display:inline-block;
      margin-right:15px;
      margin-bottom:10px;
      padding:10px 14px;
      background:#003366;
      color:#fff;
      border-radius:8px;
      text-decoration:none;
      font-size:14px;
    }

    .quick-links a:hover { background:#0055aa; }
  
  </style>   
</head>

<body class="staff-page">
<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <img src="LogoJawiUTeM_white-01.png" alt="Logo">

        <div style="text-align:center;">
            <h3><?= $_SESSION['user_name']; ?></h3>
            <p><?= $_SESSION['user_id']; ?></p>
        </div>

        <nav class="nav-links">
            <br>
            <a href="staff_home.php" style="font-weight:bold;">🏠 Dashboard<br><br></a>
            <a href="staff_view_app.php">📝 Student Application<br><br></a>
            <a href="staff_reg_college.php">🏢 Register College & Block<br><br></a>
            <a href="staff_reg_room.php">🚪 Register Residence/Rooms<br><br></a>
            <a href="staff_assign_room.php">🔑 Room Assignment<br><br></a>
            <a href="staff_report.php">📊 Report<br><br></a>
        </nav>

        <form action="logout.php" method="post" style="margin-top:auto;">
            <button type="submit" class="signout-btn">Sign Out</button>
        </form>
    </div>

    <!-- DASHBOARD CONTENT -->
    <div class="dashboard-container">
        <h2>Staff Dashboard</h2>
        <p class="subtitle">Overview of hostel application and assignment status</p>

        <!-- KPI SUMMARY -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <h4>Total Applications</h4>
                <p><?= $totalApplications ?></p>
            </div>

            <div class="kpi-card">
                <h4>Approved Applications</h4>
                <p><?= $approvedApplications ?></p>
            </div>

            <div class="kpi-card">
                <h4>Pending Applications</h4>
                <p><?= $pendingApplications ?></p>
            </div>

            <div class="kpi-card">
                <h4>Rejected Applications</h4>
                <p><?= $rejectedApplications ?></p>
            </div>

            <div class="kpi-card">
                <h4>Students Assigned to Rooms</h4>
                <p><?= $totalStudentsAssigned ?></p>
            </div>
        </div>

        <!-- QUICK LINKS -->
        <div class="quick-links">
            <h3>Quick Actions</h3>
            <a href="staff_view_app.php">Review Applications</a>
            <a href="staff_assign_room.php">Assign Rooms</a>
            <a href="staff_report.php">View Reports</a>
        </div>

    </div>
</div>
</body>
</html>