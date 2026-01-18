<?php
session_start();
include 'db_conn.php';

// Ensure user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$matrix_no = $_SESSION['matrix_no'] ?? $_SESSION['user_id'];

$sql = "
SELECT 
    h.application_id,
    h.matrix_no,
    h.session,
    h.reason,
    h.details,
    h.status,
    h.reject_reason,
    s.student_name,
    s.program,
    c.college_name
FROM hostel_application h
JOIN student s ON h.matrix_no = s.matrix_no
LEFT JOIN college_assignment ca ON ca.matrix_no = h.matrix_no
LEFT JOIN college c ON ca.college_id = c.college_id
WHERE h.matrix_no = ?
ORDER BY h.application_id DESC
LIMIT 1
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
  <title>Application Result - e-Kolej UTeM</title>
  <link rel="stylesheet" href="style.css">
  <style>

    .section-title { 
        font-size: 18px; color: #003366; font-weight: bold; 
    }
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px;box-shadow: 0 0 5px rgba(0,0,0,0.1); overflow: hidden; }
    th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
    td.label { background: #f4f4f4; font-weight: bold; width: 30%; }
    .status-box { margin-bottom: 20px; background: #f0f8ff; padding: 20px; border-radius: 10px; }
    .status-approved { color: green; font-weight: bold; }
    .status-rejected { color: red; font-weight: bold; }
    .no-application { text-align: center; color: gray; margin-top: 30px; }
    .status-pending { color: orange; font-weight: bold; }

  </style>
</head>

<body class="student-page">
    <div class="container">
      <?php include 'stud_sidebar.php'; ?>
      
      <div class="dashboard-container">
        <h2>Hostel Application Result</h2>
        
        <?php if ($application): ?>
          <div class="status-box">
            
           <p>Status:
            <span class="<?= $application['status'] === 'Approved' ? 'status-approved' : 
            ($application['status'] === 'Rejected' ? 'status-rejected' : 'status-pending')
            ?>">
            <?= htmlspecialchars($application['status']) ?>
            </span>
          </p>
          

<?php if ($application['status']==='Approved'): ?>
<p><strong>Assigned College:</strong> <?= htmlspecialchars($application['college_name'] ?? '-') ?></p>
<?php elseif ($application['status']==='Rejected'): ?>
<p><strong>Reject Reason:</strong> <?= htmlspecialchars($application['reject_reason']) ?></p>
<?php endif; ?>

        </div>
        <div class="section-title">Application Information</div>
        <table>
          <tr><td class="label">Student Name</td><td><?php echo $application['student_name']; ?></td></tr>
          <tr><td class="label">Matrix No</td><td><?php echo $application['matrix_no']; ?></td></tr>
          <tr><td class="label">Program</td><td><?php echo $application['program']; ?></td></tr>
          <tr><td class="label">Session</td><td><?php echo $application['session']; ?></td></tr>
          <tr><td class="label">Reason</td><td><?php echo $application['reason']; ?></td></tr>
          <tr><td class="label">Details</td><td><?= !empty($application['details']) ? htmlspecialchars($application['details']) : '-' ?></td></tr>
        </table>
        
        <?php else: ?>
          <p class="no-application">No hostel application result found.</p>
          <?php endif; ?>
      </div>
    </div>
  </body>
</html>
