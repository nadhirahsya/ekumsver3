<?php
session_start();
include 'db_conn.php';

// Check if the user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get student data from DB
$matrix_no = $_SESSION['matrix_no'] ?? $_SESSION['user_id'];
$current_session = "1-2025/2026";
// ================= GET STUDENT =================
$sql = "SELECT * FROM student WHERE matrix_no = '$matrix_no'";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
} else {
    $student = [
        'student_name' => '',
        'matrix_no' => '',
        'faculty' => '',
        'cohort' => '',
        'program' => '',
        'ic_no' => '',
        'student_phone' => '',
        'race' => '',
        'religion' => ''
    ];
}

// ================= CHECK EXISTING APPLICATION =================
$check_sql = "SELECT status FROM hostel_application 
              WHERE matrix_no = ? AND session = ? 
              LIMIT 1";

$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "ss", $matrix_no, $current_session);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

$already_applied = false;
$existing_status = null;

if ($row = mysqli_fetch_assoc($check_result)) {
    $already_applied = true;
    $existing_status = $row['status'];
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hostel Application - e-Kolej UTeM</title>
  <link rel="stylesheet" href="/e_kolej/style.css">
  <style>
  
    .form-table {
      width: 100%;
      border-collapse: collapse;
    }
    .form-table td {
      padding: 8px 12px;
      vertical-align: middle;
    }    
    input[type="text"], input[type="number"], select, 
    textarea {
      padding: 8px;
      font-size: 14px; 
      border-radius: 6px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }
    .submit-btn {
      background-color: #007BFF;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .submit-btn:hover {
      background-color: #0056b3;
    }

    .medical-row {
      visibility: hidden; height: 0;
    }
    
    .medical-row.active {
      visibility: visible; height: auto;
    }
    
    .form-table textarea {
      width: 100%;
      resize: vertical;
    }
    .form-table select,.form-table input[type="file"] {
      width: 100%;
    }
    .form-table input[readonly] {
      width: 100%;
      padding: 8px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      background-color: #f5f5f5;
    }
  </style>
</head>


<body class="student-page">
  
  <!-- ================= MAIN CONTENT ================= -->
   <div class="container">
      <?php include 'stud_sidebar.php'; ?>
      
      <div class="dashboard-container">
        <h2>Hostel Application</h2>
        <form action="stud_process_application.php" method="POST" enctype="multipart/form-data">
        
      <!-- ================= STUDENT DETAILS ================= -->
       <p class="form-title">Student Details</p>

       <table class="form-table">
       
          <tr>
            <td>Matric No</td>
            <td><input type="text" value="<?= htmlspecialchars($student['matrix_no']) ?>" readonly /></td>
            <td>Name</td>
            <td class="name-field" style="width: 600px;">
            <input type="text" value="<?= htmlspecialchars($student['student_name']) ?>" readonly /></td>
          </tr>

          <tr>
            <td>Cohort</td>
            <td><input type="text" value="<?= htmlspecialchars($student['cohort']) ?>" readonly /></td>
            <td>Faculty</td>
            <td><input type="text" value="<?= htmlspecialchars($student['faculty']) ?>" readonly /></td>
          </tr>

          <tr>
            <td>Year/Sem</td>
            <td><input type="text" value="<?= htmlspecialchars($student['academic_year']) ?>" readonly /></td>
            <td>Race</td>
            <td><input type="text" value="<?= htmlspecialchars($student['race']) ?>" readonly /></td>
          </tr>

          <tr>
            <td>Phone No</td>
            <td><input type="text" value="<?= htmlspecialchars($student['student_phone']) ?>" readonly /></td>
            <td>Religion</td>
            <td><input type="text" value="<?= htmlspecialchars($student['religion']) ?>" readonly /></td>
          </tr>

          <tr>
              <td>IC No</td>
          <td><input type="text" value="<?= htmlspecialchars($student['ic_no']) ?>" readonly></td>
             <td>Program</td>
            <td><input type="text" value="<?= htmlspecialchars($student['program']) ?>" readonly /></td>
          </tr>
        </table>

        <!-- ================= APPLICATION DETAILS ================= -->
        <p class="form-title">Application Details</p>

        <table class="form-table">

        <tr>
          <td>Academic Session :</td>
          <td>
            <select name="session" required>
              <option value="">Select Session (Semester-Session)</option>
              <option value="1-2025/2026">1-2025/2026</option>
            </select>
          </td>
        </tr>
         
          <tr>
            <td>Reason Apply :</td>
            <td>
              <select name="reason" id="reason" required>
                <option value="">Choose Reason</option>
                <option value="Internet & Resources">Difficulty with Internet & Resources</option>
                <option value="Lab Equipment Use">Require Special Equipment / Lab Use</option>
                <option value="Final Year Student">Final Year Student</option>
                <option value="Medical Condition">Medical Condition</option>
                <option value="Others">Other</option>
              </select>
            </td>
          </tr>

          <tr id="detailsRow">
            <td>Details :</td>
            <td>
              <textarea name="details" id="detailsText" rows="4"
              placeholder="Explain your situation (required for Others / Medical)"></textarea>
            </td>
          </tr>
          
          <tr id="medicalRow" class="medical-row">
            <td>Medical Evidence :</td>
            <td>
              <input type="file" name="medical_doc" id="medicalFile" accept=".pdf,.jpg,.png">
              <small style="color:red;">* Required for Medical reason</small>
            </td>
          </tr>
        </table>

        <button type="submit" class="submit-btn">Submit Application</button>

        <input type="hidden" name="matrix_no" value="<?= htmlspecialchars($student['matrix_no']) ?>">
      </form>
    </div>  
  </div>
  
  <script>
  const reasonSelect = document.getElementById("reason");
  const detailsText = document.getElementById("detailsText");
  const medicalRow = document.getElementById("medicalRow");
  const medicalFile = document.getElementById("medicalFile");
  
  reasonSelect.addEventListener("change", function () {
    const value = this.value;
    // Reset
    detailsText.required = false;
    medicalFile.required = false;
    medicalRow.classList.remove("active");
    
    if (value === "Others") {
    detailsText.required = true;}
    if (value === "Medical Condition") {
      detailsText.required = true;
      medicalFile.required = true;
      medicalRow.classList.add("active");
    }
  });
  </script>
  
</body>
</html>
