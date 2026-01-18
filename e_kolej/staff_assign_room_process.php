<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    exit("Unauthorized access");
}
$staff_id = $_SESSION['user_id'];
$room_id  = $_POST['room_id'] ?? '';
$house_id = $_POST['house'] ?? '';
$matrix_no = $_POST['student'] ?? '';
$checkin = $_POST['date'] ?? date('Y-m-d');

if (!$room_id || !$matrix_no) {
    exit("Invalid data");
}
/* ===============================
   1️⃣ Get room + house details
================================ */
$sql = "
SELECT 
    r.room_id, r.capacity, r.current_capacity,
    h.house_id, h.actual_load, h.current_load
FROM room r
JOIN house h ON r.house_id = h.house_id
WHERE r.room_id = ? 
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) exit("Room not found");

/* ===============================
    Capacity validation
================================ */
if ($data['current_capacity'] >= $data['capacity']) {
    exit("Room already full");
}
if ($data['current_load'] >= $data['actual_load']) {
    exit("Residence already full");
}

/* ===============================
    Check student already assigned
================================ */
$check = $conn->prepare("SELECT * FROM student_room WHERE matrix_no=?");
$check->bind_param("s", $matrix_no);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    exit("Student already assigned to a room");
}
$check->close();

/* ===============================
    Insert student-room
================================ */
$insert = $conn->prepare("
INSERT INTO student_room (matrix_no, room_id, checkin_date)
VALUES (?, ?, ?)
");
$insert->bind_param("sis", $matrix_no, $data['room_id'], $checkin);
$insert->execute();
$insert->close();

/* ===============================
    Update room capacity
================================ */
$conn->query("
UPDATE room 
SET current_capacity = current_capacity + 1 
WHERE room_id = {$data['room_id']}
");

/* ===============================
    Update house load
================================ */
$conn->query("
UPDATE house 
SET current_load = current_load + 1 
WHERE house_id = {$data['house_id']}
");

echo "Student successfully registered into room";
