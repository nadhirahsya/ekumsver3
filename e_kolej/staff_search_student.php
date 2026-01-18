<?php
include 'db_conn.php';
header('Content-Type: application/json');

$matrix_no = $_GET['matrix_no'] ?? '';
if ($matrix_no == '') {
    echo json_encode(["error" => true, "message" => "Matrix number required"]);
    exit;
}

// Query ambil student + latest approved college_assignment
$stmt = $conn->prepare("
SELECT 
  s.matrix_no,
  s.student_name,
  s.faculty,
  s.program,
  s.cohort,
  s.academic_year,
    c.college_id,
    c.college_name,
    b.block_id,
    b.block_no,
    b.gender
FROM student s
JOIN hostel_application ha 
    ON s.matrix_no = ha.matrix_no 
    AND ha.status = 'Approved'
JOIN college_assignment ca 
    ON s.matrix_no = ca.matrix_no
JOIN college c 
    ON ca.college_id = c.college_id
JOIN block b 
    ON ca.block_id = b.block_id
WHERE s.matrix_no = ?
LIMIT 1
");

$stmt->bind_param("s", $matrix_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["error" => true, "message" => "Student not found or not approved"]);
    exit;
}

echo json_encode($result->fetch_assoc());