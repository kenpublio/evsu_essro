<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$conn = getDB();
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT id, username, fullname, student_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count - ONE per submission
$total_query = "
    SELECT COUNT(DISTINCT CONCAT(DATE(r.submitted_at), '-', IFNULL(r.service_type_id, 0))) as total
    FROM responses r 
    WHERE r.user_id = ?
";
$stmt = $conn->prepare($total_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_result = $stmt->get_result();
$total = $total_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total / $limit);
$stmt->close();

// Get evaluation history - ONE row per submission with ALL comments combined
$query = "
    SELECT 
        MIN(r.id) as id,
        MAX(r.submitted_at) as submitted_at,
        st.name as service_name,
        ROUND(AVG(r.rating), 1) as rating,
        GROUP_CONCAT(DISTINCT r.answer SEPARATOR ' | ') as comment
    FROM responses r
    JOIN service_types st ON r.service_type_id = st.id
    WHERE r.user_id = ?
    GROUP BY DATE(r.submitted_at), r.service_type_id
    ORDER BY submitted_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$evaluations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get overall stats
$stats_query = "
    SELECT 
        COUNT(DISTINCT CONCAT(DATE(submitted_at), '-', IFNULL(service_type_id, 0))) as total_evaluations,
        ROUND(AVG(rating), 1) as avg_rating,
        MAX(submitted_at) as last_evaluation,
        COUNT(DISTINCT service_type_id) as services_evaluated
    FROM responses
    WHERE user_id = ?
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Set default values if no stats
if (!$stats) {
    $stats = [
        'total_evaluations' => 0,
        'avg_rating' => 0,
        'last_evaluation' => null,
        'services_evaluated' => 0
    ];
}

$page_title = 'My Evaluation History - EVSU';
?>

<!-- Your HTML remains the same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --evsu-red: #8B0000;
            --evsu-gold: #FFD700;
            --evsu-dark: #1a1a1a;
            --evsu-gray: #f5f5f5;
            --success-green: #28a745;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .evsu-header {
            background: var(--evsu-red);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-section img {
            height: 60px;
            width: auto;
        }

        .title-section h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
        }

        .title-section .subtitle {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-actions a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-actions a:hover {
            background: rgba(255,255,255,0.2);
        }

        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            color: var(--evsu-red);
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--evsu-dark);
        }

        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Evaluation Card */
        .evaluation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .evaluation-card:hover {
            transform: translateY(-3px);
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--evsu-red);
        }

        .service-badge {
            background: var(--evsu-red);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .date {
            color: #666;
            font-size: 0.85rem;
        }

        .date i {
            margin-right: 5px;
        }

        .card-body {
            padding: 20px;
        }

        .rating-section {
            margin-bottom: 15px;
        }

        .rating-stars {
            display: inline-flex;
            gap: 3px;
        }

        .rating-stars i {
            font-size: 1.2rem;
        }

        .rating-stars i.fa-star {
            color: #ffd700;
        }

        .rating-stars i.far {
            color: #ddd;
        }

        .rating-value {
            margin-left: 10px;
            font-weight: 600;
            color: var(--evsu-dark);
        }

        .comment-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .comment-section i {
            color: var(--evsu-red);
            margin-right: 8px;
        }

        .comment-section p {
            color: #555;
            line-height: 1.5;
        }

        .no-comment {
            color: #999;
            font-style: italic;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-link {
            padding: 8px 15px;
            background: white;
            color: var(--evsu-red);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .page-link:hover {
            background: var(--evsu-red);
            color: white;
        }

        .page-link.active {
            background: var(--evsu-red);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #666;
            font-size: 1.1rem;
        }

        .btn-back {
            display: inline-block;
            background: var(--evsu-red);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: white;
            opacity: 0.8;
            font-size: 0.9rem;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header class="evsu-header">
        <div class="header-container">
            <div class="logo-section">
                <img src="../images/EVSU_Official_Logo.png" alt="EVSU Logo">
                <div class="title-section">
                    <h1>EVSU - Ormoc Campus</h1>
                    <div class="subtitle">My Evaluation History</div>
                </div>
            </div>
            <div class="header-actions">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="../logout.php" onclick="return confirm('Logout?');">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-clipboard-list"></i>
                <div class="value"><?php echo $stats['total_evaluations'] ?? 0; ?></div>
                <div class="label">Total Evaluations</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-star"></i>
                <div class="value"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></div>
                <div class="label">Average Rating</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar"></i>
                <div class="value"><?php echo $stats['last_evaluation'] ? date('M d, Y', strtotime($stats['last_evaluation'])) : 'Never'; ?></div>
                <div class="label">Last Evaluation</div>
            </div>
        </div>

        <?php if (!empty($evaluations)): ?>
            <?php foreach ($evaluations as $eval): ?>
                <div class="evaluation-card">
                    <div class="card-header">
                        <span class="service-badge">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($eval['service_name']); ?>
                        </span>
                        <span class="date">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('F j, Y, g:i A', strtotime($eval['submitted_at'])); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="rating-section">
                            <div class="rating-stars">
                                <?php $rating = intval($eval['rating'] ?? 0); ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $rating): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value"><?php echo $rating; ?>/5</span>
                        </div>
                        <div class="comment-section">
    <i class="fas fa-comment"></i>
    <?php 
    // Get comment from answer column (per question) or combined
    $comment_text = trim($eval['comment'] ?? '');
    
    if (!empty($comment_text) && $comment_text !== 'NULL'): 
    ?>
        <p><?php echo nl2br(htmlspecialchars($comment_text)); ?></p>
    <?php else: ?>
        <p class="no-comment">No comment provided</p>
    <?php endif; ?>
</div>


                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-link active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>" class="page-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>You haven't submitted any evaluations yet.</p>
                <p style="font-size: 0.9rem; margin-top: 10px;">Go to your dashboard to evaluate Registrar services.</p>
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Go to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p><i class="fas fa-copyright"></i> <?php echo date('Y'); ?> EVSU Registrar Evaluation System | All Rights Reserved</p>
    </footer>
</body>
</html>