<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();
$functions = new Functions();

// Get current user data (FIXED: removed duplicate)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get Registrar office
$registrar = $functions->getRegistrarOffice();
$office_id = $registrar['id'];

// Get parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$service_filter = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$export = isset($_GET['export']);
$print = isset($_GET['print']);

// Parse month
$year = (int)substr($month, 0, 4);
$month_num = (int)substr($month, 5, 2);
$month_name = date('F', mktime(0, 0, 0, $month_num, 1, $year));

// Build query with service filter
$query = "
    SELECT 
        DATE(r.submitted_at) as eval_date,
        u.fullname as student_name,
        u.student_id,
        COALESCE(st.name, 'General') as service_name,
        ROUND(AVG(r.rating), 1) as avg_rating,
        GROUP_CONCAT(DISTINCT r.answer SEPARATOR ' | ') as comments,
        COUNT(r.id) as question_count
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

$query .= " GROUP BY DATE(r.submitted_at), r.user_id, r.service_type_id ORDER BY r.submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$evaluations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get summary statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_submissions,
        COUNT(DISTINCT r.user_id) as total_respondents,
        ROUND(AVG(r.rating), 1) as avg_rating
    FROM responses r
    WHERE r.office_id = ? 
    AND YEAR(r.submitted_at) = ? 
    AND MONTH(r.submitted_at) = ?
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("iii", $office_id, $year, $month_num);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate statistics by service
$service_stats = [];
$total_ratings = 0;
$rating_sum = 0;

foreach ($evaluations as $eval) {
    if ($eval['avg_rating']) {
        $rating_sum += $eval['avg_rating'];
        $total_ratings++;
    }
    
    $service = $eval['service_name'] ?? 'Unknown';
    if (!isset($service_stats[$service])) {
        $service_stats[$service] = ['count' => 0, 'sum' => 0];
    }
    $service_stats[$service]['count']++;
    $service_stats[$service]['sum'] += $eval['avg_rating'];
}

$avg_rating = $total_ratings > 0 ? round($rating_sum / $total_ratings, 2) : 0;

// Get all service types for filter
$services_result = $conn->query("SELECT * FROM service_types WHERE is_active = 1 ORDER BY name");
$services = $services_result ? $services_result->fetch_all(MYSQLI_ASSOC) : [];

// Handle CSV Export
if ($export) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="registrar_report_' . $month . '.csv"');
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['Date', 'Student Name', 'Student ID', 'Service Type', 'Average Rating', 'Comments', 'Questions Count']);
    
    foreach ($evaluations as $eval) {
        fputcsv($output, [
            $eval['eval_date'],
            $eval['student_name'] ?? 'Unknown',
            $eval['student_id'] ?? 'N/A',
            $eval['service_name'] ?? 'Unknown',
            $eval['avg_rating'] ?? '',
            $eval['comments'] ?? '',
            $eval['question_count'] ?? ''
        ]);
    }
    
    fclose($output);
    exit();
}

// Handle Print View
if ($print) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Print Report - <?php echo $month_name; ?> <?php echo $year; ?></title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            @media print { .no-print { display: none; } }
            h2 { color: #8B0000; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background: #8B0000; color: white; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
            <button onclick="window.print()">🖨️ Print</button>
            <button onclick="window.close()">❌ Close</button>
        </div>
        
        <h2>EVSU - Ormoc Campus</h2>
        <h3>Registrar's Office Monthly Report</h3>
        <p><?php echo $month_name; ?> <?php echo $year; ?></p>
        <p>Generated on: <?php echo date('F d, Y h:i A'); ?></p>
        
        <h3>Summary Statistics</h3>
        <table>
            <thead>
                <tr><th>Metric</th><th>Value</th></tr>
            </thead>
            <tbody>
                <tr><td>Total Evaluations</td><td><?php echo count($evaluations); ?></td></tr>
                <tr><td>Total Respondents</td><td><?php echo count(array_unique(array_column($evaluations, 'student_id'))); ?></td></tr>
                <tr><td>Average Rating</td><td><?php echo $avg_rating; ?>/5</td></tr>
            </tbody>
        </table>
        
        <h3>Detailed Evaluations</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Service</th>
                    <th>Rating</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $eval): ?>
                <tr>
                    <td><?php echo $eval['eval_date']; ?></td>
                    <td><?php echo htmlspecialchars($eval['student_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($eval['student_id'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($eval['service_name'] ?? 'General'); ?></td>
                    <td><?php echo $eval['avg_rating']; ?>/5</td>
                    <td><?php echo htmlspecialchars(substr($eval['comments'] ?? 'No comments', 0, 100)); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($evaluations)): ?>
                <tr><td colspan="6" style="text-align: center;">No evaluations found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>EVSU Registrar Evaluation System &copy; <?php echo date('Y'); ?></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$page_title = 'Monthly Reports - Registrar Evaluation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --evsu-red: #8B0000; --evsu-gold: #FFD700; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        
        .header {
            background: var(--evsu-red);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .back-button-container {
            margin-bottom: 20px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            color: var(--evsu-red);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        
        .back-button:hover {
            background: var(--evsu-red);
            color: white;
            transform: translateX(-5px);
            border-color: var(--evsu-red);
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .filter-bar {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .form-control {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: var(--evsu-red);
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--evsu-red);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .rating-stars {
            color: #ffd700;
            display: inline-flex;
            gap: 2px;
            margin-left: 5px;
        }
        
        .chart-container {
            height: 300px;
            margin: 20px 0;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            background: #e9ecef;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .search-container {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-container input {
            position: relative;
            flex: 1;
            max-width: 350px;
            padding: 10px 12px 10px 35px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .search-container input:focus {
            outline: none;
            border-color: var(--evsu-red);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-chart-bar"></i> Registrar Monthly Reports</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></span>
        </div>
    </div>

    <div class="container">
        <div class="back-button-container">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="card">
            <h2 style="margin-bottom: 20px;">
                <?php echo $month_name; ?> <?php echo $year; ?> Report
            </h2>
            
            <div class="filter-bar">
                <form method="GET" style="display: flex; gap: 10px; flex: 1; flex-wrap: wrap;">
                    <input type="month" name="month" class="form-control" value="<?php echo $month; ?>">
                    
                    <select name="service" class="form-control">
                        <option value="0">All Services</option>
                        <?php foreach ($services as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $service_filter == $s['id'] ? 'selected' : ''; ?>>
                                <?php echo $s['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generate
                    </button>
                    
                    <a href="export.php?month=<?php echo $month; ?>&service_id=<?php echo $service_filter; ?>" class="btn btn-success">
    <i class="fas fa-download"></i> Export CSV
        </a>
                    
                    <a href="?month=<?php echo $month; ?>&service=<?php echo $service_filter; ?>&print=1" class="btn btn-info" target="_blank">
                        <i class="fas fa-print"></i> Print
                    </a>
                </form>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($evaluations); ?></div>
                    <div>Total Evaluations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $avg_rating; ?></div>
                    <div>Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($service_stats); ?></div>
                    <div>Services Rated</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_ratings; ?></div>
                    <div>Questions Answered</div>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="ratingChart"></canvas>
            </div>
            
            <h3 style="margin: 20px 0;">Service Performance</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Service Type</th>
                        <th>Evaluations</th>
                        <th>Average Rating</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($service_stats as $service => $stats): ?>
                        <?php $service_avg = $stats['count'] > 0 ? round($stats['sum'] / $stats['count'], 2) : 0; ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($service); ?></strong></td>
                            <td><?php echo $stats['count']; ?></td>
                            <td>
                                <?php echo $service_avg; ?> / 5
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($service_avg)): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $percent = ($service_avg / 5) * 100;
                                if ($percent >= 80) echo '<span style="color: #28a745;">Excellent</span>';
                                elseif ($percent >= 60) echo '<span style="color: #17a2b8;">Good</span>';
                                elseif ($percent >= 40) echo '<span style="color: #ffc107;">Average</span>';
                                else echo '<span style="color: #dc3545;">Needs Improvement</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h3 style="margin: 30px 0 20px;">Detailed Evaluations</h3>

<!-- Search Bar for Detailed Evaluations -->
<div class="search-container">
    <div style="position: relative; flex: 1; max-width: 350px;">
        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
        <input type="text" id="detailedSearchInput" placeholder="Search by student, ID, service, or comments...">
    </div>
    <button onclick="clearDetailedSearch()" class="btn btn-secondary">
        <i class="fas fa-times"></i> Clear
    </button>
    <span id="detailedSearchCount" style="color: #666; font-size: 13px;"></span>
</div>

<?php if (empty($evaluations)): ?>
    <p>No evaluations found for this period.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table" id="detailedEvaluationsTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Service</th>
                    <th>Questions</th>
                    <th>Rating</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody id="detailedTableBody">
                <?php foreach ($evaluations as $eval): ?>
                    <tr class="detailed-row">
                        <td><?php echo date('M d, Y', strtotime($eval['eval_date'])); ?></td>
                        <td class="det-name"><?php echo htmlspecialchars($eval['student_name'] ?? 'Unknown'); ?></td>
                        <td class="det-id"><?php echo htmlspecialchars($eval['student_id'] ?? 'N/A'); ?></td>
                        <td class="det-service"><?php echo htmlspecialchars($eval['service_name'] ?? 'General'); ?></td>
                        <td class="det-questions">10 questions</td>
                        <td class="det-rating">
                            <?php echo $eval['avg_rating'] ?? 'N/A'; ?>/5
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($eval['avg_rating'] ?? 0)): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td class="det-comments">
    <?php 
    $has_comment = !empty($eval['comments']) && $eval['comments'] !== 'NULL';
    if ($has_comment): 
        echo '<span style="color: #28a745;">✓ With feedback</span>';
    else: 
        echo '<span style="color: #999;">No feedback</span>';
    endif; 
    ?>
</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div style="margin-top: 25px; text-align: right;">
    <a href="index.php" class="btn btn-primary" style="background: #6c757d;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

    <div class="footer">
        <p>© <?php echo date('Y'); ?> EVSU Registrar Evaluation System</p>
    </div>

    <script>
        // Chart for service performance
        const ctx = document.getElementById('ratingChart').getContext('2d');
        const chartLabels = [<?php foreach ($service_stats as $service => $stats): ?> '<?php echo addslashes($service); ?>', <?php endforeach; ?>];
        const chartData = [<?php foreach ($service_stats as $service => $stats): ?> <?php echo $stats['count'] > 0 ? round($stats['sum'] / $stats['count'], 2) : 0; ?>, <?php endforeach; ?>];
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Average Rating',
                    data: chartData,
                    backgroundColor: '#8B0000',
                    borderColor: '#8B0000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        title: {
                            display: true,
                            text: 'Average Rating (1-5)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Service Type'
                        }
                    }
                }
            }
        });
        
        // Search functionality for Detailed Evaluations table
        function searchDetailedTable() {
            const input = document.getElementById('detailedSearchInput');
            const filter = input.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#detailedTableBody .detailed-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.querySelector('.det-name')?.textContent.toLowerCase() || '';
                const id = row.querySelector('.det-id')?.textContent.toLowerCase() || '';
                const service = row.querySelector('.det-service')?.textContent.toLowerCase() || '';
                const comments = row.querySelector('.det-comments')?.textContent.toLowerCase() || '';
                const questions = row.querySelector('.det-questions')?.textContent.toLowerCase() || '';
                
                const matchesSearch = filter === '' || 
                    name.includes(filter) || 
                    id.includes(filter) || 
                    service.includes(filter) || 
                    comments.includes(filter) ||
                    questions.includes(filter);
                
                if (matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const totalRows = rows.length;
            const searchCount = document.getElementById('detailedSearchCount');
            if (searchCount) {
                if (filter === '') {
                    searchCount.innerHTML = `<i class="fas fa-database"></i> Showing ${totalRows} of ${totalRows} entries`;
                } else {
                    searchCount.innerHTML = `<i class="fas fa-search"></i> Found ${visibleCount} matching entries out of ${totalRows}`;
                }
            }
        }
        
        function clearDetailedSearch() {
            const input = document.getElementById('detailedSearchInput');
            input.value = '';
            searchDetailedTable();
            input.focus();
        }
        
        // Event listener
        document.getElementById('detailedSearchInput').addEventListener('keyup', searchDetailedTable);
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            searchDetailedTable();
        });
    </script>
</body>
</html>