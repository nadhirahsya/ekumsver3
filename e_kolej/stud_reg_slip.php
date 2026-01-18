<?php
session_start();
include 'db_conn.php';
date_default_timezone_set('Asia/Kuala_Lumpur'); // Waktu Malaysia
$printed_at = date('d-m-Y H:i:s');


// Ensure user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$matrix_no = $_SESSION['matrix_no'] ?? $_SESSION['user_id'];

$sql = "
SELECT 
    h.session,
    h.reason,
    h.details,
    h.status,
    s.student_name,
    s.ic_no,
    s.program,
    s.faculty,
    c.college_name
FROM hostel_application h
JOIN student s ON h.matrix_no = s.matrix_no
LEFT JOIN college_assignment ca ON ca.matrix_no = h.matrix_no
LEFT JOIN college c ON ca.college_id = c.college_id
WHERE h.matrix_no = ? AND h.status = 'Approved'
ORDER BY h.application_id DESC
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matrix_no);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pre-Registration Slip - e-Kolej UTeM</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .content { 
        flex-grow: 1; padding: 40px; 
        background-color: #f8f8f8; color: blue; 
    }

    .slip-box {
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      max-width: 900px;
      margin: auto;
    }

    .slip-box h2 {
      text-align: center;
      color: #003366;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    td.label {
      width: 35%;
      background-color: #f0f0f0;
      font-weight: bold;
      padding: 12px;
    }

    td.value {
      padding: 12px;
      background-color: #fafafa;
    }

    .status-approved {
      color: green;
      font-weight: bold;
    }
    
    .status-rejected { color: red; font-weight: bold; }

    .no-app {
      text-align: center;
      color: gray;
      padding: 30px;
    }

    .print-btn {
      display: block;
      margin: 30px auto 0 auto;
      padding: 10px 25px;
      background-color: #003366;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }

    .print-btn:hover {
      background-color: #002244;
    }

    @media print {
      .print-btn, .sidebar {
    display: none !important;
  }

  .container {
    display: block !important;
  }

  .dashboard-container {
    margin-left: 0 !important;
    padding: 0 !important;
    width: 100% !important;
  }

  .slip-box {
    margin: 0 auto !important;
    max-width: 700px;
  }

      body {
        background: white;
      }
    }

    .slip-header {
  text-align: center;
  margin-bottom: 25px;
}

.utem-logo {
  width: 110px;      
  height: auto;
  margin-bottom: 10px;
}
.status-pending { color: orange; font-weight: bolcd; }
    .slip-remarks {
      margin-top: 25px;
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 10px;
      font-size: 14px;
      color: #333;
    }

    .slip-remarks p,
    .slip-remarks ol {
      margin: 5px 0;
    }

    .slip-remarks ol {
      padding-left: 20px;
    }

    .slip-remarks .important {
      color: red;
      font-weight: bold;
    }


  </style>
</head>

<body class="student-page">
  <div class="container">
    <?php include 'stud_sidebar.php'; ?>
      
    <div class="dashboard-container">
       <h2>Pre Registration Slip</h2>
       <?php if ($application): ?>
        <div class="slip-box">
          <div class="slip-header">
            <img src="logoutem.png" alt="UTeM Logo" class="utem-logo">
            <h2>UTeM Hostel Pre-Registration Slip</h2>
          </div>
      
          <table>
            <tr><td class="label">Matric Number</td><td class="value"><?= $matrix_no ?></td></tr>
            <tr><td class="label">IC Number</td><td class="value"><?= $application['ic_no'] ?></td></tr>
            <tr><td class="label">Full Name</td><td class="value"><?= $_SESSION['user_name'] ?></td></tr>
            <tr><td class="label">Program</td><td class="value"><?= $application['program'] ?></td></tr>
            <tr><td class="label">Faculty</td><td class="value"><?= $application['faculty'] ?></td></tr>
            <tr><td class="label">Session</td><td class="value"><?= $application['session'] ?></td></tr>
            <tr><td class="label">Reason</td><td class="value"><?= $application['reason'] ?></td></tr>
            <tr><td class="label">Details</td><td class="value"><?= !empty($application['details']) ? $application['details'] : '-' ?></td></tr>
           <tr>
            <td class="label">Application Status</td>
            <td class="value <?= $application['status'] === 'Approved' ? 'status-approved' : ($application['status'] === 'Rejected' ? 'status-rejected' : 'status-pending') ?>">
              <?= htmlspecialchars($application['status']) ?>
            </td>
          </tr>
          <tr><td class="label">College</td><td class="value"><?= $application['college_name'] ?? '-' ?></td></tr>
       </table>

    
          <button class="print-btn" onclick="window.print()">🖨️ Print Slip</button>
          <p style="text-align: center; margin-top: 15px;"><strong>Slip Printed at:</strong> <?= $$printed_at = date('j F Y, h:i A'); ?></p>
        </div>
        <?php else: ?>
          <div class="no-app">
            <p>You don't have any <strong>approved</strong> hostel application yet.</p>
          </div>
          <?php endif; ?>
      
     <!-- Remarks / Reminder Section -->
        <div class="slip-remarks">
          <p><strong>Remarks / Reminder:</strong></p>
          <p><em>*This decision is subject to current rules and the permitted student intake of the college.</em></p>
          <p><strong>Applicant Guidelines:</strong></p>
          <ol>
            <li>Not involved in any disciplinary cases.</li>
            <li>Not allowed to bring any vehicle.</li>
            <li>Be prepared to pay the accommodation fee.</li>
            <li>Please ensure all updated information is accurate.</li>
          </ol>
          <p class="important">** IMPORTANT!!</p>
          <ol class="important">
            <li>Students who successfully obtain a place in the college are given 3 days from the registration date to complete registration.</li>
            <li>Students who do not register on the specified date will be considered unsuccessful if no notice is given to the hostel management.</li>
          </ol>
        </div>

      </div>
    </div>
</body>
</html>
