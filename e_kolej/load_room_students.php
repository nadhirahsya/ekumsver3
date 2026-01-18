<?php
include 'db_conn.php';

$room_id = $_GET['room_id'] ?? '';
if (!$room_id) exit;

$stmt = $conn->prepare("
    SELECT 
        s.matrix_no,
        s.student_name,
        sr.checkin_date
    FROM student_room sr
    JOIN student s ON sr.matrix_no = s.matrix_no
    WHERE sr.room_id = ?
");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<tr><td colspan='3'>No students in this room</td></tr>";
    exit;
}

while ($row = $result->fetch_assoc()) {
    echo "
    <tr>
        <td>{$row['matrix_no']}</td>
        <td>{$row['student_name']}</td>
        <td>{$row['checkin_date']}</td>
    </tr>
    ";
}
