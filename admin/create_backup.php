<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    header("Location: login.php");
    exit();
}
$conn = getDB();
// Get user data
$stmt = $conn->prepare("SELECT id, username, fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = '';
$error = '';
$backup_file = '';

// Create backup directory if not exists
$backup_dir = dirname(__DIR__) . '/backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . 'backup_' . $timestamp . '.sql';
    
    // Get database credentials from config
    global $db_host, $db_user, $db_password, $db_name;
    
    // Create backup using mysqldump
    $command = sprintf(
        'mysqldump -h%s -u%s -p%s %s > %s 2>&1',
        $db_host,
        $db_user,
        $db_password,
        $db_name,
        $backup_file
    );
    
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($backup_file)) {
        $message = "Backup created successfully!";
        
        // Log the backup action
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ({$_SESSION['user_id']}, 'create_backup', '$ip', '$agent')");
        
        // Delete old backups (keep last 30)
        $backups = glob($backup_dir . 'backup_*.sql');
        if (count($backups) > 30) {
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            $to_delete = array_slice($backups, 0, count($backups) - 30);
            foreach ($to_delete as $file) {
                unlink($file);
            }
        }
    } else {
        $error = "Backup failed: " . implode("\n", $output);
    }
}

// Get list of existing backups
$backups = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . 'backup_*.sql');
    foreach ($files as $file) {
        $backups[] = [
            'file' => basename($file),
            'size' => round(filesize($file) / 1024 / 1024, 2) . ' MB',
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

$page_title = 'Create Backup - Registrar Evaluation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f0f0; font-family: 'Segoe UI', sans-serif; }
        .header { background: #8B0000; color: white; padding: 15px 20px; margin-bottom: 20px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
        .btn-backup { background: #8B0000; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-backup:hover { background: #a00000; }
        .btn-download { background: #28a745; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .footer { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-database"></i> Database Backup Manager</h2>
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($user['fullname'] ?? $user['username'] ?? 'Admin'); ?></span>
                    <a href="index.php" class="btn btn-outline-light ms-3">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Create New Backup</h3>
            <p>Create a full database backup of the EVSU Evaluation System. Backups are stored in the /backups/ directory.</p>
            
            <form method="POST">
                <button type="submit" name="create_backup" class="btn-backup" onclick="return confirm('Create a new database backup? This may take a few moments.')">
                    <i class="fas fa-database"></i> Create Backup Now
                </button>
            </form>
        </div>

        <div class="card">
            <h3><i class="fas fa-history"></i> Existing Backups</h3>
            
            <?php if (empty($backups)): ?>
                <p>No backups found. Click "Create Backup Now" to create your first backup.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Date Created</th>
                            <th>Size</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?php echo $backup['file']; ?></td>
                                <td><?php echo $backup['date']; ?></td>
                                <td><?php echo $backup['size']; ?></td>
                                <td>
                                    <a href="download_backup.php?file=<?php echo urlencode($backup['file']); ?>" class="btn-download">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="text-muted mt-3"><small>Keeping last 30 backups automatically. Old backups are deleted.</small></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-info-circle"></i> Backup Information</h3>
            <ul>
                <li>Backups include all database tables, structure, and data.</li>
                <li>Backup files are stored in: <code><?php echo $backup_dir; ?></code></li>
                <li>Auto-delete old backups: Keeps last 30 backups only.</li>
                <li>To restore a backup, go to <a href="restore_backup.php">Restore Backup</a> page.</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        <p>© <?php echo date('Y'); ?> EVSU Registrar Evaluation System | All Rights Reserved</p>
    </div>
</body>
</html>
