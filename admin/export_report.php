<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$conn = getDB();
$office_id = 20;
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$service_filter = isset($_GET['service']) ? (int)$_GET['service'] : 0;

$year = substr($month, 0, 4);
$month_num = substr($month, 5, 2);

// FIXED QUERY - GROUP BY submission to avoid duplicates
$query = "
    SELECT 
        MAX(DATE(r.submitted_at)) as eval_date,
        u.fullname as student_name,
        u.student_id,
        u.email,
        COALESCE(st.name, 'General') as service_name,
        ROUND(AVG(r.rating), 1) as rating,
        GROUP_CONCAT(DISTINCT r.answer SEPARATOR ' | ') as comment,
        MAX(r.submitted_at) as submitted_at
    FROM responses r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN service_types st ON r.service_type_id = st.id
    WHERE r.office_id = ? 
    AND YEAR(r.submitted_at) = ? 
    AND MONTH(r.submitted_at) = ?
";

$params = [$office_id, $year, $month_num];
$types = "iii";

if ($service_filter > 0) {
    $query .= " AND r.service_type_id = ?";
    $params[] = $service_filter;
    $types .= "i";
}

$query .= " GROUP BY r.user_id, DATE(r.submitted_at), r.service_type_id ORDER BY submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Clear any output buffers
if (ob_get_level()) ob_clean();

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="registrar_report_' . $month . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

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

// Add data rows - ONE ROW PER SUBMISSION
foreach ($results as $row) {
    fputcsv($output, [
        $row['student_name'],
        $row['student_id'],
        $row['email'],
        $row['service_name'],
        $row['rating'] . '/5',
        $row['comment'] ?? '',
        date('d/m/Y H:i', strtotime($row['submitted_at']))
    ]);
}

fclose($output);
exit();
?>