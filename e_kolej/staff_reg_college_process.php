<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_code = strtoupper(trim($_POST['college_code']));
    $college_name = ucwords(trim($_POST['college_name']));

    // Validate
    if (empty($college_code) || empty($college_name)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }
    // Check duplicate college code OR name
    $check = $conn->prepare("SELECT * FROM college WHERE college_code = ? OR college_name = ?");
    $check->bind_param("ss", $college_code, $college_name);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('College code or college name already exists!'); window.history.back();</script>";
        exit();
    }
    // ================= INSERT =================
    $stmt = $conn->prepare("INSERT INTO college (college_code, college_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $college_code, $college_name);

    if ($stmt->execute()) {
        $_SESSION['message'] = "College added successfully!";
        header("Location: staff_reg_college.php");
        exit();
    } else {
        echo "<script>alert('Error adding college.'); window.history.back();</script>";
    }
}
?>
