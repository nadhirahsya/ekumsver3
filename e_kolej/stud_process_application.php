<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$matrix_no = $_POST['matrix_no'];
$session = $_POST['session'];
$reason = $_POST['reason'];
$details = $_POST['details'];
$status = 'Pending';
$applied_date = date('Y-m-d H:i:s');

/* ================= BASIC VALIDATION ================= */
if ($session === '' || $reason === '') {
    echo "<script>alert('Please complete all required fields.'); window.history.back();</script>";
    exit();
}

/* ================= CONDITIONAL VALIDATION ================= */
if (($reason === 'Others' || $reason === 'Medical Condition') && $details === '') {
    echo "<script>alert('Details are required for the selected reason.'); window.history.back();</script>";
    exit();
}

/* ================= MEDICAL FILE VALIDATION ================= */
$medical_doc_path = null;

if ($reason === 'Medical Condition') {

    if (!isset($_FILES['medical_doc']) || $_FILES['medical_doc']['error'] !== 0) {
        echo "<script>alert('Medical document is required.'); window.history.back();</script>";
        exit();
    }

    // Limit file size to 2MB
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($_FILES['medical_doc']['size'] > $maxSize) {
        echo "<script>alert('File terlalu besar. Maksimum 2MB.'); window.history.back();</script>";
        exit();
    }

    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($_FILES['medical_doc']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        echo "<script>alert('Invalid file type. Upload PDF or Image only.'); window.history.back();</script>";
        exit();
    }

    $upload_dir = "uploads/medical/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_filename = $matrix_no . "_" . time() . "." . $file_ext;
    $medical_doc_path = $upload_dir . $new_filename;

     if (!move_uploaded_file($_FILES['medical_doc']['tmp_name'], $medical_doc_path)) {
        echo "<script>alert('Failed to upload medical document.'); window.history.back();</script>";
        exit();
    }
}


// Check if already applied for the session
// ================= PREVENT DUPLICATE APPLICATION =================
$check_sql = "SELECT application_id 
              FROM hostel_application 
              WHERE matrix_no = ? AND session = ? 
              LIMIT 1";

$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "ss", $matrix_no, $session);
mysqli_stmt_execute($check_stmt);
$check_res = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_res) > 0) {
    echo "
    <script>
        alert('You have already submitted a hostel application for this session.\\nResubmission is not allowed.');
        window.location.href = 'stud_apply_hostel.php';
    </script>";
    exit();
}

/* ================= INSERT APPLICATION ================= */
$sql = "INSERT INTO hostel_application
        (matrix_no, session, reason, details, medical_doc, status, applied_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssss", 
    $matrix_no, $session, $reason, $details, $medical_doc_path, $status, $applied_date);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Application submitted successfully!'); window.location.href='stud_apply_hostel.php';</script>";
} else {
    echo "<script>alert('Failed to submit application.'); window.history.back();</script>";
}
?>
