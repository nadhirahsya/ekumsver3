<?php
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];
    $room_no = trim($_POST['room_no']);

    if (!empty($application_id) && !empty($room_no)) {
        $stmt = mysqli_prepare($conn, "UPDATE hostel_application SET room_no = ? WHERE application_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $room_no, $application_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Room number assigned successfully.'); window.location.href='staff_assign_room.php';</script>";
        } else {
            echo "<script>alert('Failed to assign room.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Missing data.'); window.history.back();</script>";
    }
}
?>
