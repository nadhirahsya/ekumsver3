<?php
include 'db_conn.php';

$block_id = $_GET['block_id'] ?? '';
$selected_house = $_GET['selected_house'] ?? ''; // new GET param

if (!$block_id) {
    echo "<option value=''>-- Select House --</option>";
    exit;
}

$stmt = $conn->prepare("
    SELECT house_id, house_code
    FROM house
    WHERE block_id = ?
");
$stmt->bind_param("i", $block_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<option value=''>-- Select House --</option>";
while ($r = $result->fetch_assoc()) {
    $sel = ($r['house_id'] == $selected_house) ? "selected" : "";
    echo "<option value='{$r['house_id']}' $sel>{$r['house_code']}</option>";
}
