<?php
session_start();
include 'db_conn.php';

// ✅ Pastikan staff login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    die("Staff session not found. Please login again.");
}

$staff_id = $_SESSION['staff_id'];
$app_id = $_POST['application_id'] ?? '';
$action = $_POST['action'] ?? '';
$college_id = $_POST['college_id'] ?? null;
$block_id   = $_POST['block_id'] ?? null;

// Jika tiada application_id atau action, redirect
if ($app_id === '' || $action === '') {
    header("Location: staff_view_app.php");
    exit();
}
/* ================= GET MATRIX NO ================= */
$stmt = $conn->prepare("
    SELECT matrix_no 
    FROM hostel_application 
    WHERE application_id = ?
");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Student not found.");
}

$row = $res->fetch_assoc();
$matrix_no = $row['matrix_no'];

/* ================= GET CURRENT STATUS ================= */
$stmt = $conn->prepare("
    SELECT status 
    FROM hostel_application 
    WHERE application_id=?
");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$statusRes = $stmt->get_result();

if ($statusRes->num_rows === 0) {
    die("Application not found.");
}

$currentStatus = $statusRes->fetch_assoc()['status'];

// ❌ Block reject after approved
if ($currentStatus === 'Approved' && $action === 'Rejected') {
    die("This application has already been approved and cannot be rejected.");
}

// ❌ Block re-approve rejected application
if ($currentStatus === 'Rejected' && $action === 'Approved') {
    die("Rejected application cannot be approved again.");
}


/* ================= APPROVE ================= */
if ($action === 'Approved') {

    // Validate both college & block
    if (empty($college_id) || empty($block_id)) {
        die("Please select both college and block before approving.");
    }

    // Update hostel_application
    $stmt = $conn->prepare("
        UPDATE hostel_application
        SET status='Approved', staff_id=?, reject_reason=NULL
        WHERE application_id=?
    ");
    $stmt->bind_param("si", $staff_id, $app_id);
    $stmt->execute();

    // Check if student already has college_assignment (prevent duplicate)
    $stmt_check = $conn->prepare("
        SELECT * FROM college_assignment 
        WHERE matrix_no = (SELECT matrix_no FROM hostel_application WHERE application_id=?)
    ");
    $stmt_check->bind_param("i", $app_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Update existing assignment with college_id AND block_id
        $stmt_update = $conn->prepare("
            UPDATE college_assignment
            SET college_id=?, block_id=?, staff_id=?, assigned_date=NOW()
            WHERE matrix_no = (SELECT matrix_no FROM hostel_application WHERE application_id=?)
        ");
        $stmt_update->bind_param("iisi", $college_id, $block_id, $staff_id, $app_id);
        $stmt_update->execute();
    } else {
        // Insert new assignment
        $stmt_insert = $conn->prepare("
            INSERT INTO college_assignment (matrix_no, college_id, block_id, assigned_date, staff_id)
            SELECT matrix_no, ?, ?, NOW(), ? FROM hostel_application WHERE application_id=?
        ");
        $stmt_insert->bind_param("iisi", $college_id, $block_id, $staff_id, $app_id);
        $stmt_insert->execute();
    }
/* ================= REJECT ================= */
} elseif ($action === 'Rejected') {

    $reason = $_POST['reject_reason'] ?? '';

    if (empty($reason)) {
        die("Please provide a reason for rejection.");
    }

    // Update hostel_application status to Rejected
    $stmt = $conn->prepare("
        UPDATE hostel_application
        SET status='Rejected', reject_reason=?, staff_id=?
        WHERE application_id=?
    ");
    $stmt->bind_param("ssi", $reason, $staff_id, $app_id);
    $stmt->execute();

    // Optional: remove college assignment if any
    $stmt_del = $conn->prepare("
        DELETE FROM college_assignment
        WHERE matrix_no = (SELECT matrix_no FROM hostel_application WHERE application_id=?)
    ");
    $stmt_del->bind_param("i", $app_id);
    $stmt_del->execute();
}

header("Location: staff_view_app.php");
exit();
?>
