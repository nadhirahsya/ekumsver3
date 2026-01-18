<?php
include 'db_conn.php';

$college_id = $_GET['college_id'] ?? '';
$selected_block = $_GET['selected_block'] ?? '';

if(!$college_id){
    echo '<option value="">-- Select Block --</option>';
    exit;
}

$stmt = $conn->prepare("SELECT block_id, block_no FROM block WHERE college_id=? ORDER BY block_no");
$stmt->bind_param("i", $college_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">-- Select Block --</option>';
while($row = $result->fetch_assoc()){
    $sel = ($row['block_id'] == $selected_block) ? "selected" : "";
    echo "<option value='{$row['block_id']}' $sel>{$row['block_no']}</option>";
}
