<?php
include 'db_conn.php';

$house_id = $_GET['house_id'] ?? '';
if (!$house_id) exit;

$stmt = $conn->prepare("
    SELECT room_id, room_no, capacity, current_capacity,
           (capacity - current_capacity) AS availability
    FROM room
    WHERE house_id = ?
");
$stmt->bind_param("i", $house_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<tr><td colspan='4'>No room found</td></tr>";
    exit;
}

while ($r = $result->fetch_assoc()) {

    // Add gray background for full rooms, but still clickable
    $style = ($r['availability'] <= 0) ? "style='opacity:0.5; background:#eee'" : "";

    // Add a note "(Full)" next to room number
    $room_no_display = ($r['availability'] <= 0) ? $r['room_no'] . " (Full)" : $r['room_no'];

    echo "
    <tr $style onclick=\"chooseRoom(
        '{$r['room_id']}',
        '{$r['room_no']}',
        '{$house_id}'
    )\">
        <td>{$room_no_display}</td>
        <td>{$r['capacity']}</td>
        <td>{$r['current_capacity']}</td>
        <td>{$r['availability']}</td>
    </tr>";
}
?>
