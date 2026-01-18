<?php
session_start();
include 'db_conn.php';

// Ensure this page is accessed via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST['application_id'] ?? null;
    $assigned_college = $_POST['assigned_college'] ?? null;

    // Validation
    if (!$application_id || !$assigned_college) {
        echo "<script>alert('Missing data. Please try again.'); window.location.href='staff_view_app.php';</script>";
        exit();
    }

    // Update the assigned_college in hostel_application table
    $stmt = $conn->prepare("UPDATE hostel_application SET assigned_college = ? WHERE application_id = ?");
    $stmt->bind_param("si", $assigned_college, $application_id);

    if ($stmt->execute()) {
        echo "<script>alert('College assigned successfully.'); window.location.href='staff_view_app.php';</script>";
    } else {
        echo "<script>alert('Failed to assign college.'); window.location.href='staff_view_app.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid access
    header("Location: staff_view_app.php");
    exit();
}
?>
