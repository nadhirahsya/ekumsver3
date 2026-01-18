<?php
session_start();
include 'db_conn.php'; // Your DB connection file

$matrix_no = $_SESSION['matrix_no']; // Assuming student is logged in

// Fetch student application(s)
$sql = "SELECT * FROM hostel_applications WHERE matrix_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matrix_no);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Check Application Status</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* SAME STYLE as you provided earlier */
    /* ... truncated for brevity ... */
  </style>
</head>
<body>
<div class="container">
  <div class="sidebar">
    <img src="LogoJawiUTeM_white-01.png" alt="UTeM Logo">
    <h4>WELCOME!</h4>
    <h4><?php echo $_SESSION['student_name']; ?></h4>
    <h4><?php echo $_SESSION['program']; ?></h4>

    <a href="apply_hostel.html">🏠 Hostel Application</a>
    <a href="check_status.php">📄 Check Application Status</a>
    <a href="application_result.html">📃 Application Result</a>
    <a href="pre_registration_slip.html">📝 Pre-Registration Slip</a>

    <button class="signout-btn">Sign Out</button>
  </div>

  <div class="content">
    <h2>Check Hostel Application Status</h2>

    <div class="section-title">Butiran Pelajar / Student Details</div>

    <?php if (count($applications) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>No.</th>
          <th>Date Applied</th>
          <th>Previous College</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="application-table">
        <?php foreach ($applications as $index => $app): ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= $app['date_applied'] ?></td>
          <td><?= $app['previous_college'] ?></td>
          <td><span style="color: blue;"><?= ucfirst($app['status']) ?></span></td>
          <td>
            <button class="btn-view" onclick="showDetails(<?= $index ?>)">View</button>
            <button class="btn-close-inline" onclick="hideDetails()">Close</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Application details area -->
    <div class="details-box" id="application-details" style="display:none;">
      <h3>Application Details</h3>
      <table>
        <tr><td class="label">Matrix No:</td><td><?= $matrix_no ?></td></tr>
        <tr><td class="label">IC No:</td><td><?= $_SESSION['ic_no'] ?></td></tr>
        <tr><td class="label">Student's Name:</td><td><?= $_SESSION['student_name'] ?></td></tr>
        <tr><td class="label">Program:</td><td><?= $_SESSION['program'] ?></td></tr>
        <tr><td class="label">Kolej Sebelum Ini:</td><td><?= $applications[0]['previous_college'] ?></td></tr>
        <tr><td class="label">Jantina:</td><td><?= $_SESSION['gender'] ?></td></tr>
        <tr><td class="label">Tarikh Permohonan:</td><td><?= $applications[0]['date_applied'] ?></td></tr>
      </table>

      <div class="section-title">Maklumat Permohonan</div>
      <table>
        <tr><td class="label">Session Semester:</td><td><?= $applications[0]['session'] ?></td></tr>
        <tr><td class="label">Reason Apply:</td><td><?= $applications[0]['reason'] ?></td></tr>
        <tr><td class="label">Details:</td><td><?= $applications[0]['additional_details'] ?></td></tr>
      </table>
    </div>

    <?php else: ?>
      <p class="no-application">You have not submitted any hostel application yet.</p>
    <?php endif; ?>
    
    <script>
      function showDetails(index = 0) {
        document.getElementById("application-details").style.display = "block";
      }
      function hideDetails() {
        document.getElementById("application-details").style.display = "none";
      }
    </script>
  </div>
</div>
</body>
</html>
