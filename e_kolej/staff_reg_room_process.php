<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $house_code = trim($_POST['house_code'] ?? '');
    $room_suffix = trim($_POST['room_no'] ?? ''); // A, B, C
    $capacity = intval($_POST['capacity'] ?? 0);

    if (!$house_code || !$room_suffix || $capacity < 1) {
        echo "<script>alert('Please fill all fields correctly.'); window.history.back();</script>";
        exit();
    }

    // Validate suffix
    if (!preg_match("/^[A-Z]$/i", $room_suffix)) {
        echo "<script>alert('Room code must be A-Z only.'); window.history.back();</script>";
        exit();
    }

    // Get house_id
    $stmt = $conn->prepare("SELECT house_id FROM house WHERE house_code = ?");
    $stmt->bind_param("s", $house_code);
    $stmt->execute();
    $stmt->bind_result($house_id);
    $stmt->fetch();
    $stmt->close();

    if (!$house_id) {
        echo "<script>alert('Residence not found.'); window.history.back();</script>";
        exit();
    }

    // Build full room_no
    $room_no = $house_code . '-' . strtoupper($room_suffix);

    // Duplicate check
    $stmt = $conn->prepare(
        "SELECT room_id FROM room WHERE house_id = ? AND room_no = ?"
    );
    $stmt->bind_param("is", $house_id, $room_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Room already exists in this residence.'); window.history.back();</script>";
        exit();
    }
    $stmt->close();

    // Register a room
    $stmt = $conn->prepare(
        "INSERT INTO room (house_id, room_no, capacity, current_capacity, status)
         VALUES (?, ?, ?, 0, 'AVAILABLE')"
    );
    $stmt->bind_param("isi", $house_id, $room_no, $capacity);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Room successfully added.'); window.location='staff_reg_room.php';</script>";
}
?>
