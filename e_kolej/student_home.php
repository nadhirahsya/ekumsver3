<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Fetch student info
$matrix_no = $_SESSION['user_id'];
$student = $conn->query("SELECT * FROM student WHERE matrix_no='$matrix_no'")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Dashboard - e-Kolej UTeM</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .dashboard-container { flex:1; padding: 20px; }
        .info-box { margin: 20px 0; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Paragraph styling */
        p { margin: 10px 0; line-height: 1.5; }

        /* Headings inside info-box */
        .info-box h3 { margin-bottom: 10px; }

        /* Important reminders list styling */
        .important-reminders {
            font-size: 14px;       /* small font size */
            padding-left: 25px;    /* move bullets slightly to the right */
            margin-top: 10px;
        }
        .important-reminders li {
            margin-bottom: 8px;    /* gap between each item */
            line-height: 1.4;
        }

        .important { color: red; font-weight: bold; }

                /* Highlighted text for deadlines */
        .highlight { color: #d10510ff; font-weight: bold; }
    </style>
</head>
<body class="student-page">
    <div class="container">
        <?php include 'stud_sidebar.php'; ?>

        <div class="dashboard-container">
            <h2>Student Dashboard</h2>
            <p>Welcome to E-Kolej UTeM Management System, <?php echo $student['student_name']; ?>!</p>

            <div class="info-box">
                <h3>Hostel Application Info</h3>
                <p>The hostel application for <span class="highlight">Session 2025/2026</span> is open from <span class="highlight">1 January 2026</span> to <span class="highlight">22 January 2026</span>.</p>
                <p>Please prepare your documents and complete your application within the given period.</p>
            </div>

            <div class="info-box">
                <h3 class="important">Important Reminders!</h3>
                <ul class="important-reminders">
                    <li>Only Year 1 students are eligible for college accommodation by default.</li>
                    <li>Ensure your personal details are correct before submitting the application.</li>
                    <li>Results are subject to current regulations and the allowed student intake of the college.</li>
                    <li>Download your pre-registration slip after approval.</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
