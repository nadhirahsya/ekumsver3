<?php
include 'db_conn.php';

$block_id = $_GET['block_id'] ?? '';
if (!$block_id) {
    echo "<tr><td colspan='4'>Block not selected</td></tr>";
    exit;
}

$stmt = $conn->prepare("
    SELECT house_id, house_code, actual_load, current_load,
           (actual_load - current_load) AS availability
    FROM house
    WHERE block_id = ?
");
$stmt->bind_param("i", $block_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<tr><td colspan='4'>No residence found</td></tr>";
    exit;
}

while ($r = $result->fetch_assoc()) {
    echo "
    <tr onclick=\"loadRooms(this, {$r['house_id']})\">
        <td>{$r['house_code']}</td>
        <td>{$r['actual_load']}</td>
        <td>{$r['current_load']}</td>
        <td>{$r['availability']}</td>
    </tr>";
}
