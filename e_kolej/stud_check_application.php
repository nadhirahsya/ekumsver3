<?php
session_start();
include 'db_conn.php';

// Check if the user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get student data from DB using matrix number stored in session
$matrix_no = $_SESSION['matrix_no'] ?? $_SESSION['user_id'];

$sql = "
SELECT h.*, s.student_name, s.ic_no, s.program, s.faculty, s.gender
FROM hostel_application h
JOIN student s ON h.matrix_no = s.matrix_no
WHERE h.matrix_no = ?
ORDER BY h.application_id DESC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $matrix_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$application = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Check Hostel Application Status - e-Kolej UTeM</title>
  <link rel="stylesheet" href="style.css">

  <style> 
    
   
    .section-title { 
        font-size: 18px; margin: 20px 0 10px; 
        color: #003366; font-weight: bold; 
    }
    table { 
        width: 100%; border-collapse: collapse; background-color: white; 
        border-radius: 8px; overflow: hidden; box-shadow: 0 0 5px rgba(0,0,0,0.1); 
    }
    th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; 
        text-align: left; vertical-align: middle; 
    }
    th { 
        color: blue; 
    }
    td.label { 
        font-weight: bold; color: black; 
        width: 30%; background-color: #f0f0f0; 
    }
    .btn-view, .btn-close-inline { 
        background-color: #0077b6; color: white; padding: 5px 10px; 
        border: none; border-radius: 5px; cursor: pointer; margin-right: 5px; 
    }
    .btn-view:hover, .btn-close-inline:hover { 
        background-color: #023e8a; 
    }
    .details-box { 
        margin-top: 30px; background-color: #f0f8ff; 
        padding: 20px; border-radius: 10px; display: none; 
    }
    .no-application { 
        text-align: center; color: gray; margin-top: 30px; 
    }
</style>
</head>

<body class= "student-page">
  <div class="container">
    <?php include 'stud_sidebar.php'; ?>
    
    <div class="dashboard-container">
    <h2>Check Hostel Application Status</h2>

    <?php if ($application): ?>
      <div class="section-title">Student Application Summary</div>
      <table>
        <thead>
          <tr>
            <th>No.</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="application-table">
          <tr>
            <td>1</td>
            <td><?php echo htmlspecialchars($application['applied_date']); ?></td>
            <td><span style="color: blue;"> <?php echo htmlspecialchars($application['status']); ?> </span></td>
            
            <td>
              <button class="btn-view" onclick="showDetails()">View</button>
              <button class="btn-close-inline" onclick="hideDetails()">Close</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="details-box" id="application-details">
        <div class="section-title">Student Information</div>
        <table>
          <tr><td class="label">Matric No:</td><td><?php echo $application['matrix_no']; ?></td></tr>
          <tr><td class="label">IC No:</td><td><?php echo $application['ic_no']; ?></td></tr>
          <tr><td class="label">Student's Name:</td><td><?php echo $application['student_name']; ?></td></tr>
          <tr><td class="label">Program:</td><td><?php echo $application['program']; ?></td></tr>
          <tr><td class="label">Faculty:</td><td><?php echo $application['faculty']; ?></td></tr>
          <tr><td class="label">Gender:</td><td><?php echo $application['gender']; ?></td></tr>
          <tr><td class="label">Applied Date:</td><td><?php echo $application['applied_date']; ?></td></tr>
        </table>
        <div class="section-title">Application Details</div>
        <table>
          <tr><td class="label">Session Semester:</td><td><?php echo $application['session']; ?></td></tr>
          <tr><td class="label">Reason Apply:</td><td><?php echo $application['reason']; ?></td></tr>
          <tr><td class="label">Details:</td><td>
            <?php 
            // Show '-' if details is empty
            echo (!empty($application['details'])) ? htmlspecialchars($application['details']) : '-'; 
            ?>
            </td>
          </tr>
        </table>
      </div>

    <?php else: ?>
      <p class="no-application">You have not submitted any hostel application yet.</p>
    <?php endif; ?>

    <script>
      function showDetails() {
        document.getElementById("application-details").style.display = "block";
      }
      function hideDetails() {
        document.getElementById("application-details").style.display = "none";
      }
    </script>
  </div>
</body>
</html>
