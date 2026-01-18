<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $college_id = intval($_POST['college_id'] ?? 0);
    $block_no   = strtoupper(trim($_POST['block_no'] ?? ''));
    $gender     = ucfirst(trim($_POST['gender'] ?? ''));

    // ================= VALIDATION =================
    if ($college_id === 0 || $block_no === '' || $gender === '') {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    // Check duplicate block under same college
    $check = $conn->prepare("
        SELECT * FROM block 
        WHERE college_id = ? AND block_no = ?
    ");
    $check->bind_param("is", $college_id, $block_no);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('This block already exists for the selected college.'); window.history.back();</script>";
        exit();
    }

    // ================= INSERT =================
    $stmt = $conn->prepare("
        INSERT INTO block (college_id, block_no, gender)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $college_id, $block_no, $gender);

    if ($stmt->execute()) {
        header("Location: staff_reg_college.php");
        exit();
    } else {
        echo "<script>alert('Error adding block.'); window.history.back();</script>";
    }
}
?>
