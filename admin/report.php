<?php
session_start();  
require_once '../includes/config.php';
require_once '../includes/functions.php';
$conn = getDB();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Manually get Registrar office ID
$office_query = $conn->query("SELECT id FROM offices WHERE name LIKE '%Registrar%' LIMIT 1");
if ($office_query && $office_query->num_rows > 0) {
    $office_id = $office_query->fetch_assoc()['id'];
} else {
    $office_id = 1;
}

// Get user data
$stmt = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch available services
$services = [];
$service_query = "SELECT id, name FROM service_types WHERE is_active = 1 ORDER BY name";
$service_result = $conn->query($service_query);
if ($service_result) {
    while ($row = $service_result->fetch_assoc()) {
        $services[] = $row;
    }
}

// ============================================
// HANDLE REPORT PARAMETERS
// ============================================
$report_type = isset($_GET['type']) ? $_GET['type'] : 'overall';
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');
$service_filter = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$export = isset($_GET['export']);
$print = isset($_GET['print']);

// ============================================
// BUILD WHERE CLAUSE
// ============================================
$where_clause = "r.office_id = $office_id";
$params = [];
$types = "";

if ($service_filter > 0) {
    $where_clause .= " AND r.service_type_id = ?";
    $params[] = $service_filter;
    $types .= "i";
}

if ($report_type === 'monthly') {
    $year = substr($month, 0, 4);
    $month_num = substr($month, 5, 2);
    $where_clause .= " AND YEAR(r.submitted_at) = ? AND MONTH(r.submitted_at) = ?";
    $params[] = $year;
    $params[] = $month_num;
    $types .= "ii";
} elseif ($report_type === 'custom') {
    $where_clause .= " AND DATE(r.submitted_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

// ============================================
// GET REPORT DATA
// ============================================
// ============================================
// GET REPORT DATA
// ============================================
$query = "
    SELECT r.*, 
           u.fullname, 
           u.username, 
           u.student_id,
           DATE(r.submitted_at) as eval_date,
           st.name as service_name
    FROM responses r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN service_types st ON r.service_type_id = st.id
    WHERE $where_clause
    ORDER BY r.submitted_at DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ============================================
// GET SUMMARY STATISTICS
// ============================================
$stats_query = "
    SELECT 
        COUNT(DISTINCT r.user_id) as total_respondents,
        COUNT(r.id) as total_responses,
        AVG(r.rating) as avg_rating,
        COUNT(DISTINCT DATE(r.submitted_at)) as active_days,
        MAX(r.rating) as max_rating,
        MIN(r.rating) as min_rating
    FROM responses r
    WHERE $where_clause
";
$stmt = $conn->prepare($stats_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$summary_stats = $stmt->get_result()->fetch_assoc();

// ============================================
// GET RATING DISTRIBUTION
// ============================================
$rating_distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
foreach ($report_data as $row) {
    if (isset($row['rating']) && is_numeric($row['rating'])) {
        $rating_distribution[(int)$row['rating']]++;
    }
}

// ============================================
// GET DAILY TRENDS
// ============================================
$daily_counts = [];
foreach ($report_data as $row) {
    if (isset($row['submitted_at'])) {
        $date = date('Y-m-d', strtotime($row['submitted_at']));
        $daily_counts[$date] = ($daily_counts[$date] ?? 0) + 1;
    }
}
$daily_trends = [];
foreach ($daily_counts as $date => $count) {
    $daily_trends[] = ['date' => $date, 'count' => $count];
}
usort($daily_trends, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

// ============================================
// GET TOP STUDENTS
// ============================================
$top_students = [];
$top_students_query = "
    SELECT u.fullname, u.username, u.student_id,
           COUNT(DISTINCT DATE(r.submitted_at)) as eval_count,
           ROUND(AVG(r.rating), 1) as avg_rating
    FROM responses r
    JOIN users u ON r.user_id = u.id
    WHERE r.office_id = ?
    GROUP BY u.id
    HAVING eval_count >= 1
    ORDER BY avg_rating DESC
    LIMIT 10
";
$stmt = $conn->prepare($top_students_query);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$top_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ============================================
// GET RECENT COMMENTS
// ============================================
$recent_comments = [];
$comments_query = "
    SELECT 
        MAX(r.submitted_at) as submitted_at,
        u.fullname,
        u.username,
        u.student_id,
        MAX(r.answer) as answer,
        ROUND(AVG(r.rating), 1) as rating
    FROM responses r
    JOIN users u ON r.user_id = u.id
    WHERE r.office_id = ? 
    AND (r.answer IS NOT NULL AND r.answer != '')
    GROUP BY DATE(r.submitted_at), r.user_id, r.service_type_id
    ORDER BY submitted_at DESC
    LIMIT 20
";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$recent_comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ============================================
// GET OVERALL SUGGESTIONS & RECOMMENDATIONS
// ============================================
$recent_suggestions = [];
$suggestions_query = "
    SELECT 
        r.submitted_at,
        u.fullname,
        u.username,
        u.student_id,
        r.report_text as suggestion
    FROM reports r
    JOIN users u ON r.user_id = u.id
    WHERE r.report_text IS NOT NULL AND r.report_text != ''
    ORDER BY r.submitted_at DESC
    LIMIT 20
";
$suggestions_result = $conn->query($suggestions_query);
$recent_suggestions = $suggestions_result ? $suggestions_result->fetch_all(MYSQLI_ASSOC) : [];

// ============================================
// HANDLE CSV EXPORT
// ============================================
if ($export && isset($_GET['export']) && $_GET['export'] == 'csv') {
    $export_service = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
    $export_type = isset($_GET['type']) ? $_GET['type'] : 'overall';
    
    $service_name = 'All_Services';
    if ($export_service > 0) {
        $service_query = $conn->prepare("SELECT name FROM service_types WHERE id = ?");
        $service_query->bind_param("i", $export_service);
        $service_query->execute();
        $service_result = $service_query->get_result();
        if ($service_row = $service_result->fetch_assoc()) {
            $service_name = str_replace(' ', '_', $service_row['name']);
        }
    }
    
    $date_range = '';
    if ($export_type == 'monthly' && isset($_GET['month'])) {
        $date_range = '_' . $_GET['month'];
    } elseif ($export_type == 'custom' && isset($_GET['date_from']) && isset($_GET['date_to'])) {
        $date_range = '_' . $_GET['date_from'] . '_to_' . $_GET['date_to'];
    } else {
        $date_range = '_' . date('Y-m-d');
    }
    
    $filename = "registrar_report_{$service_name}{$date_range}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['Date', 'Student Name', 'Student ID', 'Service', 'Question', 'Rating', 'Comment']);
    
    $sql = "SELECT r.*, u.fullname, u.username, u.student_id, st.name as service_name 
            FROM responses r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN service_types st ON r.service_type_id = st.id
            WHERE r.office_id = $office_id";
    
    if ($export_service > 0) {
        $sql .= " AND r.service_type_id = $export_service";
    }
    
    if ($export_type == 'monthly' && isset($_GET['month'])) {
        $year = substr($_GET['month'], 0, 4);
        $month_num = substr($_GET['month'], 5, 2);
        $sql .= " AND YEAR(r.submitted_at) = $year AND MONTH(r.submitted_at) = $month_num";
    } elseif ($export_type == 'custom' && isset($_GET['date_from']) && isset($_GET['date_to'])) {
        $sql .= " AND DATE(r.submitted_at) BETWEEN '{$_GET['date_from']}' AND '{$_GET['date_to']}'";
    }
    
    $sql .= " ORDER BY r.submitted_at DESC";
    
    $export_data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
    foreach ($export_data as $row) {
        fputcsv($output, [
            date('Y-m-d H:i', strtotime($row['submitted_at'])),
            $row['fullname'] ?? $row['username'],
            $row['student_id'] ?? 'N/A',
            $row['service_name'] ?? 'General',
            $row['question_text'] ?? '',
            $row['rating'] ?? '',
            $row['answer'] ?? ''
        ]);
    }
    
    fclose($output);
    exit();
}

$page_title = 'Reports & Analytics - Registrar Evaluation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --evsu-red: #8B0000; --evsu-gold: #FFD700; --evsu-dark: #1a1a1a; --evsu-gray: #f5f5f5; --success-green: #28a745; --warning-orange: #fd7e14; --info-blue: #17a2b8; }
        body { font-family: 'Inter', sans-serif; background: var(--evsu-gray); min-height: 100vh; }

        .evsu-header { background: linear-gradient(135deg, var(--evsu-red) 0%, #B22222 100%); color: white; padding: 1rem 2rem; box-shadow: 0 4px 12px rgba(139,0,0,0.3); position: sticky; top: 0; z-index: 1000; }
        .header-container { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .logo-section { display: flex; align-items: center; gap: 20px; }
        .evsu-logo { width: 50px; height: 50px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--evsu-red); font-weight: bold; }
        .title-section h1 { font-size: 1.3rem; font-weight: 700; }
        .title-section .subtitle { font-size: 0.8rem; opacity: 0.9; }

        .user-info { display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.1); padding: 0.5rem 1.5rem; border-radius: 40px; }
        .user-avatar { width: 40px; height: 40px; background: var(--evsu-gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--evsu-red); font-weight: bold; }
        .user-details { line-height: 1.4; }
        .user-name { font-weight: 600; font-size: 0.95rem; }
        .user-role { font-size: 0.75rem; opacity: 0.8; }
        .user-actions { display: flex; gap: 10px; margin-top: 3px; }
        .user-actions a { color: white; text-decoration: none; font-size: 0.75rem; opacity: 0.8; }
        .user-actions a:hover { opacity: 1; color: var(--evsu-gold); }
        .table, .table-responsive table, #evaluationsTable {
    display: table !important;
    width: 100%;
    border-collapse: collapse;
}

#evaluationsTable tr {
    display: table-row !important;
}

#evaluationsTable td, 
#evaluationsTable th {
    display: table-cell !important;
    padding: 8px;
    border: 1px solid #ddd;
}

        .main-container { display: flex; max-width: 1400px; margin: 20px auto; gap: 20px; padding: 0 20px; }
        .sidebar { width: 280px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); padding: 20px 0; height: fit-content; position: sticky; top: 100px; }
        .sidebar-header { padding: 0 20px 15px; border-bottom: 2px solid #f0f0f0; margin-bottom: 15px; }
        .sidebar-header h3 { color: var(--evsu-red); font-size: 1rem; text-transform: uppercase; }
        .nav-item { display: flex; align-items: center; padding: 12px 20px; color: #666; text-decoration: none; border-left: 3px solid transparent; }
        .nav-item:hover { background: #fff5f5; color: var(--evsu-red); border-left-color: var(--evsu-red); }
        .nav-item.active { background: #fff0f0; color: var(--evsu-red); border-left-color: var(--evsu-red); }
        .nav-icon { width: 24px; margin-right: 12px; }

        .content { flex: 1; }
        .content-header { background: white; padding: 20px 25px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .content-header h2 { color: var(--evsu-dark); font-size: 1.5rem; }
        .content-header h2 i { color: var(--evsu-red); margin-right: 10px; }

        .btn { padding: 8px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; border: none; cursor: pointer; }
        .btn-primary { background: var(--evsu-red); color: white; }
        .btn-success { background: var(--success-green); color: white; }
        .btn-outline { background: white; border: 1px solid #ddd; color: #666; }

        .filter-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .filter-card h3 { color: var(--evsu-dark); font-size: 1rem; margin-bottom: 15px; }
        .filter-card h3 i { color: var(--evsu-red); margin-right: 8px; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.8rem; color: #666; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 15px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.05); }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: var(--evsu-red); }
        .stat-card .label { color: #777; font-size: 0.8rem; text-transform: uppercase; }

        .chart-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .chart-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .chart-card h3 { color: var(--evsu-dark); font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .chart-card h3 i { color: var(--evsu-red); }
        .chart-container { height: 300px; position: relative; }

        .table-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; overflow-x: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .table-card h3 { color: var(--evsu-dark); font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .table-card h3 i { color: var(--evsu-red); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8f9fa; color: #555; font-weight: 600; font-size: 0.85rem; padding: 12px; text-align: left; border-bottom: 2px solid #e9ecef; }
        .table td { padding: 12px; border-bottom: 1px solid #e9ecef; color: #666; }

        .rating-stars { display: inline-flex; gap: 2px; }
        .rating-stars i { color: #ffd700; font-size: 0.9rem; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .comment-box { background: #f8f9fa; padding: 10px; border-radius: 8px; font-style: italic; color: #555; max-width: 300px; }

        .footer { background: var(--evsu-dark); color: white; text-align: center; padding: 20px; margin-top: 30px; }
        .footer a { color: #ddd; text-decoration: none; margin: 0 15px; font-size: 0.9rem; }
        .footer a:hover { color: var(--evsu-gold); }
        .copyright { font-size: 0.85rem; color: #777; margin-top: 10px; }
        

        @media (max-width: 768px) { .main-container { flex-direction: column; } .sidebar { width: 100%; position: static; } .chart-row { grid-template-columns: 1fr; } .filter-form { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body>
    <header class="evsu-header">
        <div class="header-container">
            <div class="logo-section">
                <div class="evsu-logo"><i class="fas fa-university"></i></div>
                <div class="title-section">
                    <h1>EVSU - Registrar's Office</h1>
                    <div class="subtitle">Reports & Analytics</div>
                </div>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['fullname'] ?? 'A', 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($user['fullname'] ?? $user['username'] ?? 'Admin'); ?></div>
                    <div class="user-role">Administrator</div>
                    <div class="user-actions">
                        <a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a>
                        <span>|</span>
                        <a href="../logout.php" onclick="return confirm('Logout?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h3><i class="fas fa-building"></i> REGISTRAR MENU</h3></div>
            <a href="index.php" class="nav-item"><span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span><span>Dashboard</span></a>
            <a href="evaluations.php" class="nav-item"><span class="nav-icon"><i class="fas fa-clipboard-list"></i></span><span>All Evaluations</span></a>
            <a href="questions.php" class="nav-item"><span class="nav-icon"><i class="fas fa-question-circle"></i></span><span>Survey Questions</span></a>
            <a href="students.php" class="nav-item"><span class="nav-icon"><i class="fas fa-user-graduate"></i></span><span>Student List</span></a>
            <a href="reports.php" class="nav-item active">
                <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                <span>Reports</span>
            </a>
            <a href="logs.php" class="nav-item"><span class="nav-icon"><i class="fas fa-history"></i></span><span>Activity Logs</span></a>
        </aside>

        <main class="content">
            <div class="content-header">
                <h2><i class="fas fa-chart-bar"></i> Reports & Analytics - Registrar's Office</h2>
                <div class="header-actions">
                    <div class="service-selector-group" style="display: flex; gap: 10px; align-items: center;">
                        <select id="serviceTypeSelect" class="form-control" style="width: auto; min-width: 150px; padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="0">All Services</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>"><?php echo htmlspecialchars($service['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a href="#" id="exportBtn" class="btn btn-success" onclick="exportReport()"><i class="fas fa-download"></i> Export CSV</a>
                        <a href="#" id="printBtn" class="btn btn-outline" target="_blank"><i class="fas fa-print"></i> Print</a>
                    </div>
                </div>
            </div>
            <script>
    // Get current URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const currentType = urlParams.get('type') || 'overall';
    const currentMonth = urlParams.get('month') || '<?php echo date('Y-m'); ?>';
    const currentDateFrom = urlParams.get('date_from') || '<?php echo date('Y-m-01'); ?>';
    const currentDateTo = urlParams.get('date_to') || '<?php echo date('Y-m-t'); ?>';
    
    const serviceSelect = document.getElementById('serviceTypeSelect');
    const printBtn = document.getElementById('printBtn');
    
    function exportReport() {
        const selectedService = serviceSelect.value;
        let exportUrl = `export_report.php?service=${selectedService}&type=${currentType}`;
        
        if (currentType === 'monthly') {
            exportUrl += `&month=${currentMonth}`;
        }
        if (currentType === 'custom') {
            exportUrl += `&date_from=${currentDateFrom}&date_to=${currentDateTo}`;
        }
        
        window.open(exportUrl, '_blank');
    }
    
    function updatePrintLink() {
        const selectedService = serviceSelect.value;
        let printUrl = `print_report.php?type=${currentType}`;
        
        if (currentType === 'monthly') {
            printUrl += `&month=${currentMonth}`;
        }
        if (currentType === 'custom') {
            printUrl += `&date_from=${currentDateFrom}&date_to=${currentDateTo}`;
        }
        printUrl += `&service_id=${selectedService}`;
        printBtn.href = printUrl;
    }
    
    serviceSelect.addEventListener('change', updatePrintLink);
    updatePrintLink();
</script>

            <div class="filter-card">
                <h3><i class="fas fa-filter"></i> Generate Report</h3>
                <form method="GET" action="" class="filter-form">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="type" class="form-control" onchange="this.form.submit()">
                            <option value="overall" <?php echo $report_type === 'overall' ? 'selected' : ''; ?>>Overall Report</option>
                            <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly Report</option>
                            <option value="custom" <?php echo $report_type === 'custom' ? 'selected' : ''; ?>>Custom Date Range</option>
                        </select>
                    </div>
                    <?php if ($report_type === 'monthly'): ?>
                    <div class="form-group">
                        <label>Select Month</label>
                        <input type="month" name="month" class="form-control" value="<?php echo $month; ?>" onchange="this.form.submit()">
                    </div>
                    <?php endif; ?>
                    <?php if ($report_type === 'custom'): ?>
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="value"><?php echo number_format($summary_stats['total_respondents'] ?? 0); ?></div><div class="label">Total Respondents</div></div>
                <div class="stat-card"><div class="value"><?php echo number_format($summary_stats['total_responses'] ?? 0); ?></div><div class="label">Total Responses</div></div>
                <div class="stat-card"><div class="value"><?php echo number_format($summary_stats['avg_rating'] ?? 0, 1); ?></div><div class="label">Average Rating</div></div>
                <div class="stat-card"><div class="value"><?php echo $summary_stats['active_days'] ?? 0; ?></div><div class="label">Active Days</div></div>
                <div class="stat-card"><div class="value"><?php echo $summary_stats['max_rating'] ?? 0; ?></div><div class="label">Highest Rating</div></div>
                <div class="stat-card"><div class="value"><?php echo $summary_stats['min_rating'] ?? 0; ?></div><div class="label">Lowest Rating</div></div>
            </div>

            <div class="chart-row">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Rating Distribution</h3>
                    <div class="chart-container"><canvas id="ratingChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> Daily Evaluation Trends</h3>
                    <div class="chart-container"><canvas id="trendsChart"></canvas></div>
                </div>
            </div>
            
            <?php if (!empty($top_students)): ?>
            <div class="table-card">
                <h3><i class="fas fa-trophy"></i> Top Performing Students</h3>
                <table class="table">
                    <thead><tr><th>Student</th><th>Student ID</th><th>Evaluations</th><th>Avg Rating</th><th>Stars</th></tr></thead>
                    <tbody>
                        <?php foreach ($top_students as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['fullname'] ?? $s['username']); ?></td>
                            <td><?php echo htmlspecialchars($s['student_id'] ?? 'N/A'); ?></td>
                            <td><?php echo $s['eval_count']; ?></td>
                            <td><strong><?php echo number_format($s['avg_rating'], 1); ?></strong></td>
                            <td><div class="rating-stars"><?php for ($i = 1; $i <= 5; $i++): ?><?php if ($i <= round($s['avg_rating'])): ?><i class="fas fa-star"></i><?php else: ?><i class="far fa-star"></i><?php endif; ?><?php endfor; ?></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($recent_comments)): ?>
            <div class="table-card">
                <h3><i class="fas fa-comment"></i> Recent Feedback & Comments</h3>
                <table class="table">
                    <thead><tr><th>Date</th><th>Student</th><th>Comment</th><th>Rating</th></tr></thead>
                    <tbody>
                        <?php foreach ($recent_comments as $c): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($c['submitted_at'])); ?></td>
                            <td><?php echo htmlspecialchars($c['fullname'] ?? $c['username']); ?></td>
                            <td><div class="comment-box">"<?php echo htmlspecialchars($c['answer']); ?>"</div></td>
                            <td><div class="rating-stars"><?php for ($i = 1; $i <= 5; $i++): ?><?php if ($i <= $c['rating']): ?><i class="fas fa-star"></i><?php else: ?><i class="far fa-star"></i><?php endif; ?><?php endfor; ?></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Overall Suggestions & Recommendations Table -->
            <?php if (!empty($recent_suggestions)): ?>
            <div class="table-card">
                <h3><i class="fas fa-lightbulb"></i> Overall Suggestions & Recommendations</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Suggestion / Recommendation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_suggestions as $s): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($s['submitted_at'])); ?></td>
                            <td><?php echo htmlspecialchars($s['fullname'] ?? $s['username']); ?></td>
                            <td><?php echo htmlspecialchars($s['student_id'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="comment-box" style="max-width: 400px; white-space: pre-wrap;">
                                    "<?php echo htmlspecialchars($s['suggestion'] ?? ''); ?>"
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

    <script>
        const searchInput = document.getElementById('searchInput');
        const ratingFilter = document.getElementById('ratingFilter');
        const noResults = document.getElementById('noResults');
        const filterCount = document.getElementById('filterCount');
        
        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const ratingValue = parseInt(ratingFilter.value);
            const rows = document.querySelectorAll('.evaluation-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const student = row.getAttribute('data-student') || '';
                const studentId = row.getAttribute('data-student-id') || '';
                const question = row.getAttribute('data-question') || '';
                const rating = parseInt(row.getAttribute('data-rating')) || 0;
                
                let matchesSearch = true;
                let matchesRating = true;
                
                if (searchTerm) {
                    matchesSearch = student.includes(searchTerm) || 
                                   studentId.includes(searchTerm) || 
                                   question.includes(searchTerm);
                }
                
                if (ratingValue > 0) {
                    matchesRating = rating === ratingValue;
                }
                
                if (matchesSearch && matchesRating) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
            
            filterCount.textContent = `Showing ${visibleCount} of ${rows.length} entries`;
        }
        
        function clearFilters() {
            searchInput.value = '';
            ratingFilter.value = '0';
            filterTable();
        }
        
        searchInput.addEventListener('keyup', filterTable);
        ratingFilter.addEventListener('change', filterTable);
        
        document.addEventListener('DOMContentLoaded', function() {
            filterTable();
        });

        new Chart(document.getElementById('ratingChart'), {
            type: 'pie',
            data: { labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'], datasets: [{ data: [<?php echo $rating_distribution[1]; ?>, <?php echo $rating_distribution[2]; ?>, <?php echo $rating_distribution[3]; ?>, <?php echo $rating_distribution[4]; ?>, <?php echo $rating_distribution[5]; ?>], backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#17a2b8', '#28a745'] }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
        
        new Chart(document.getElementById('trendsChart'), {
            type: 'line',
            data: { labels: [<?php foreach ($daily_trends as $t): ?>'<?php echo date('M d', strtotime($t['date'])); ?>', <?php endforeach; ?>], datasets: [{ label: 'Evaluations', data: [<?php foreach ($daily_trends as $t): ?><?php echo $t['count']; ?>, <?php endforeach; ?>], borderColor: '#8B0000', fill: true }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>

    <style>
        #searchInput:focus { border-color: var(--evsu-red); box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1); }
        .table-responsive { max-height: 600px; overflow-y: auto; }
        .evaluation-row:hover { background: #f8f9fa; }
    </style>
</body>
</html>
