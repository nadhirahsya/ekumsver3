<?php
include 'db_conn.php';

$college_id = $_GET['college_id'] ?? '';
if (!$college_id) {
    echo "College not selected.";
    exit;
}

/* Get block-level house utilization for selected college */
$houseReport = $conn->prepare("
    SELECT b.block_no, 
           SUM(h.current_load) AS total_current,
           SUM(h.actual_load) AS total_capacity
    FROM house h
    JOIN block b ON h.block_id = b.block_id
    WHERE b.college_id = ?
    GROUP BY b.block_no
    ORDER BY b.block_no
");
$houseReport->bind_param("i", $college_id);
$houseReport->execute();
$result = $houseReport->get_result();

$blockNo = [];
$currentLoad = [];
$actualLoad = [];

$html = '<table class="report-table"><thead><tr>
<th>Block</th><th>Current Load</th><th>Total Actual Load</th><th>Usage (%)</th></tr></thead><tbody>';

while($row = $result->fetch_assoc()){
    $usage = round(($row['total_current']/$row['total_capacity'])*100,1);
    $html .= '<tr>
        <td>'.$row['block_no'].'</td>
        <td>'.$row['total_current'].'</td>
        <td>'.$row['total_capacity'].'</td>
        <td>'.$usage.'%</td>
    </tr>';

    $blockNo[] = $row['block_no'];
    $currentLoad[] = (int)$row['total_current'];
    $actualLoad[] = (int)$row['total_capacity'];
}
$html .= '</tbody></table>';

/* Add canvas for bar chart */
$html .= '<canvas id="houseChart" 
             data-housecode="'.htmlspecialchars(json_encode($blockNo)).'" 
             data-currentload="'.htmlspecialchars(json_encode($currentLoad)).'" 
             data-actualload="'.htmlspecialchars(json_encode($actualLoad)).'"></canvas>';

echo $html;
