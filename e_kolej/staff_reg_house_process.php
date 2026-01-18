<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $college_name = $_POST['college_name'] ?? '';
    $block_id     = $_POST['block_id'] ?? '';
    $house_code   = trim($_POST['house_code'] ?? '');
    $actual_load  = intval($_POST['actual_load'] ?? 0);

    if (!$college_name || !$block_id || !$house_code || $actual_load < 1) {
        echo "<script>alert('Please fill in all required fields correctly.'); window.history.back();</script>";
        exit();
    }

    // Get block_no
    $stmt_block = $conn->prepare("SELECT block_no FROM block WHERE block_id = ?");
    $stmt_block->bind_param("i", $block_id);
    $stmt_block->execute();
    $stmt_block->bind_result($block_no);
    $stmt_block->fetch();
    $stmt_block->close();

    // Validate house_code format
    $pattern = "/^" . preg_quote($block_no, '/') . "-([1-9])-([0]?[1-9]|1[0-2])$/";
    if (!preg_match($pattern, $house_code)) {
        echo "<script>alert('Invalid Residence Unit format. Must be {$block_no}-LEVEL-HOUSE'); window.history.back();</script>";
        exit();
    }

    // Prevent duplicate residence
    $stmt_check = $conn->prepare("SELECT house_id FROM house WHERE house_code = ?");
    $stmt_check->bind_param("s", $house_code);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Residence \"$house_code\" already exists!'); window.history.back();</script>";
        exit();
    }
    $stmt_check->close();

    // Insert new residence (current_load MUST stay 0)
    $stmt = $conn->prepare("
        INSERT INTO house (house_code, block_id, actual_load, current_load)
        VALUES (?, ?, ?, 0)
    ");
    $stmt->bind_param("sii", $house_code, $block_id, $actual_load);

    if ($stmt->execute()) {
        echo "<script>alert('Residence \"$house_code\" successfully registered.'); window.location='staff_reg_room.php';</script>";
    } else {
        echo "<script>alert('Error registering residence: {$conn->error}'); window.history.back();</script>";
    }
    exit();
}
?>
