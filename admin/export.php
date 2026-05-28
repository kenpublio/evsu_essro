<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();

// Get parameters from the modal form
$service_id = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

// Get service name for filename
$service_name = 'All_Services';
if ($service_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM service_types WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $service_name = str_replace(' ', '_', $row['name']);
    }
}

// Build filename with service name FIRST (this is the key fix)
if ($type == 'monthly') {
    $filename = "{$service_name}_Report_{$month}.csv";
} elseif ($type == 'custom') {
    $filename = "{$service_name}_Report_{$date_from}_to_{$date_to}.csv";
} else {
    $filename = "{$service_name}_Report_" . date('Y-m-d') . ".csv";
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Add headers
fputcsv($output, [
    'Student Name',
    'Student ID',
    'Email',
    'Service Type',
    'Rating',
    'Comment',
    'Date Submitted'
]);

// Build query
$sql = "
    SELECT 
        u.fullname,
        u.student_id,
        u.email,
        COALESCE(st.name, 'General') as service_name,
        r.rating,
        r.answer as comment,
        DATE(r.submitted_at) as eval_date
    FROM responses r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN service_types st ON r.service_type_id = st.id
    WHERE 1=1
";

if ($service_id > 0) {
    $sql .= " AND r.service_type_id = $service_id";
}

if ($type == 'monthly') {
    $year = substr($month, 0, 4);
    $month_num = substr($month, 5, 2);
    $sql .= " AND YEAR(r.submitted_at) = $year AND MONTH(r.submitted_at) = $month_num";
} elseif ($type == 'custom') {
    $sql .= " AND DATE(r.submitted_at) BETWEEN '$date_from' AND '$date_to'";
}

$sql .= " ORDER BY r.submitted_at DESC";

$results = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Add data rows
foreach ($results as $row) {
    fputcsv($output, [
        $row['fullname'] ?? 'Unknown',
        $row['student_id'] ?? 'N/A',
        $row['email'] ?? 'N/A',
        $row['service_name'] ?? 'General',
        $row['rating'] . '/5',
        $row['comment'] ?? 'No comment',
        $row['eval_date'] ?? ''
    ]);
}

fclose($output);
exit();
?>