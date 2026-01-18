<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    exit('Unauthorized');
}


// Staff download student medical doc 
$app_id = intval($_GET['app_id'] ?? 0);
$isPreview = isset($_GET['preview']);

$stmt = $conn->prepare("
    SELECT medical_doc 
    FROM hostel_application 
    WHERE application_id = ?
");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$stmt->bind_result($filePath);
$stmt->fetch();
$stmt->close();

if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    exit('File not found');
}

$filename = basename($filePath);
$mime = mime_content_type($filePath);

// CLEAR OUTPUT BUFFER (IMPORTANT)
if (ob_get_length()) {
    ob_end_clean();
}

if ($isPreview) {

    // ===== PREVIEW MODE =====
    header("Content-Type: $mime");
    header("Content-Disposition: inline; filename=\"$filename\"");
    header("X-Content-Type-Options: nosniff");

} else {

    // ===== DOWNLOAD MODE =====
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Length: " . filesize($filePath));
}

readfile($filePath);
exit;
