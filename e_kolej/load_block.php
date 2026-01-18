<?php
include 'db_conn.php';
header('Content-Type: application/json');

$college_id = $_GET['college_id'] ?? '';
if (!$college_id) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT block_id, block_no, gender FROM block WHERE college_id = ? ORDER BY block_no ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $college_id);
$stmt->execute();
$result = $stmt->get_result();

$blocks = [];
while ($r = $result->fetch_assoc()) {
    $blocks[] = [
        'block_id' => $r['block_id'],
        'block_no' => $r['block_no'],
        'gender' => $r['gender']
    ];
}

echo json_encode($blocks);
