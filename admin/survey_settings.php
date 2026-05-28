<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require admin access
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || $user_role !== 'admin') {
    header("Location: login.php");
    exit();
}
$functions = new Functions();
$user_id = $_SESSION['user_id'];

$conn = getDB();
// Get user data
$stmt = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
// ============================================
// CREATE SURVEY AVAILABILITY TABLE WITH SERVICE_TYPE_ID
// ============================================
// Get user data
$stmt = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Create survey_availability table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS survey_availability (
        id INT PRIMARY KEY AUTO_INCREMENT,
        service_type_id INT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        start_date DATE,
        end_date DATE,
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE
    )
");

// Get all service types
$services_result = $conn->query("SELECT * FROM service_types WHERE is_active = 1 ORDER BY name");

// Get current settings for all services
$settings = [];
$result = $conn->query("SELECT * FROM survey_availability");
while ($row = $result->fetch_assoc()) {
    $settings[$row['service_type_id']] = $row;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_type_id'])) {
    $service_type_id = (int)$_POST['service_type_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year'));
    
    // Check if record exists
    $check = $conn->query("SELECT id FROM survey_availability WHERE service_type_id = $service_type_id");
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE survey_availability SET is_active = ?, start_date = ?, end_date = ?, updated_by = ? WHERE service_type_id = ?");
        $stmt->bind_param("issii", $is_active, $start_date, $end_date, $user_id, $service_type_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO survey_availability (service_type_id, is_active, start_date, end_date, updated_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $service_type_id, $is_active, $start_date, $end_date, $user_id);
    }
    
    if ($stmt->execute()) {
        $message = "Survey settings updated successfully!";
        // Refresh settings
        $settings = [];
        $result = $conn->query("SELECT * FROM survey_availability");
        while ($row = $result->fetch_assoc()) {
            $settings[$row['service_type_id']] = $row;
        }
    } else {
        $error = "Failed to update settings: " . $conn->error;
    }
    $stmt->close();
}
// Get service types
$services_query = "SELECT * FROM service_types WHERE is_active = 1 ORDER BY name";
$services_result = $conn->query($services_query);
$services = [];
while ($row = $services_result->fetch_assoc()) {
    $services[] = $row;
}

// For each service, get its availability settings
foreach ($services as $service) {
    $service_id = $service['id'];
    
    // Get current settings for this service
    $settings_query = "SELECT start_date, end_date, is_active FROM survey_availability WHERE service_type_id = ?";
    $stmt = $conn->prepare($settings_query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
    
    $start_date = $settings['start_date'] ?? date('Y-m-d');
    $end_date = $settings['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
    $is_active = $settings['is_active'] ?? 1;
    

     } 
// First, check if service_type_id column exists, if not add it
$check_column = $conn->query("SHOW COLUMNS FROM survey_availability LIKE 'service_type_id'");
if ($check_column->num_rows == 0) {
    // Add service_type_id column
    $conn->query("ALTER TABLE survey_availability ADD COLUMN service_type_id INT AFTER id");
    
    // Create default records for existing service types
    $services = $conn->query("SELECT id FROM service_types");
    while ($service = $services->fetch_assoc()) {
        $check = $conn->query("SELECT id FROM survey_availability WHERE service_type_id = {$service['id']}");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO survey_availability (service_type_id, is_active, start_date, end_date) 
                VALUES ({$service['id']}, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
        }
    }
}

// Ensure table exists with correct structure
$conn->query("
    CREATE TABLE IF NOT EXISTS survey_availability (
        id INT PRIMARY KEY AUTO_INCREMENT,
        service_type_id INT NOT NULL,
        is_active BOOLEAN DEFAULT FALSE,
        start_date DATE,
        end_date DATE,
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE
    )
");

// Get all service types
$services = $conn->query("SELECT * FROM service_types ORDER BY name");

// Handle form submission for specific service
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type_id = (int)$_POST['service_type_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year'));
    
    // Check if record exists
    $check = $conn->query("SELECT id FROM survey_availability WHERE service_type_id = $service_type_id");
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE survey_availability SET is_active = ?, start_date = ?, end_date = ?, updated_by = ? WHERE service_type_id = ?");
        $stmt->bind_param("issii", $is_active, $start_date, $end_date, $user_id, $service_type_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO survey_availability (service_type_id, is_active, start_date, end_date, updated_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $service_type_id, $is_active, $start_date, $end_date, $user_id);
    }
    
    if ($stmt->execute()) {
        $message = "Survey settings updated for selected service!";
        
        // Log action
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'update_survey_settings', '$ip', '$agent')");
    } else {
        $error = "Failed to update settings: " . $conn->error;
    }
    $stmt->close();
}

// Get current settings for each service
$settings = [];
$result = $conn->query("SELECT * FROM survey_availability");
while ($row = $result->fetch_assoc()) {
    $settings[$row['service_type_id']] = $row;
}

$page_title = 'Survey Settings - Registrar Evaluation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --evsu-red: #8B0000; --evsu-gold: #FFD700; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        
        .header {
            background: var(--evsu-red);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .service-card {
            background: #f9f9f9;
            border-left: 4px solid var(--evsu-red);
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .service-card:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .service-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--evsu-red);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .card h2 {
            color: var(--evsu-red);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--evsu-red);
            outline: none;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--evsu-red);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-text {
            font-size: 1rem;
            font-weight: bold;
            margin-left: 15px;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--evsu-red);
            color: white;
        }
        
        .btn-primary:hover {
            background: #a52a2a;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: white;
        }
        
        .footer a {
            color: var(--evsu-gold);
            text-decoration: none;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .service-select {
            margin-bottom: 20px;
        }
        
        .service-select select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #e0e0e0;
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
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-building"></i> Registrar Evaluation System</h1>
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
            <h2><i class="fas fa-toggle-on"></i> Survey Availability Settings</h2>
            <p>Configure survey settings for each service type individually.</p>
        </div>
        
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php while($service = $services->fetch_assoc()): 
            $current = $settings[$service['id']] ?? null;
            $is_active = $current ? $current['is_active'] : 1;
            $start_date = $current && $current['start_date'] ? $current['start_date'] : date('Y-m-d');
            $end_date = $current && $current['end_date'] ? $current['end_date'] : date('Y-m-d', strtotime('+1 year'));
        ?>
        <div class="service-card">
            <div class="service-title">
                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($service['name']); ?></span>
                <span class="status-badge <?php echo $is_active ? 'status-active' : 'status-inactive'; ?>">
                    <i class="fas <?php echo $is_active ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php echo $is_active ? 'ACTIVE' : 'INACTIVE'; ?>
                </span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="service_type_id" value="<?php echo $service['id']; ?>">
                
                <div class="form-group">
                    <label>Survey Status</label>
                    <div style="display: flex; align-items: center;">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_active" <?php echo $is_active ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="status-text" style="color: <?php echo $is_active ? '#28a745' : '#dc3545'; ?>">
                            <?php echo $is_active ? 'ACTIVE' : 'INACTIVE'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings for <?php echo htmlspecialchars($service['name']); ?>
                </button>
            </form>
        </div>
        <?php endwhile; ?>
        
        <div class="card">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>How it works:</strong><br>
                - Each service type has its own survey availability settings<br>
                - Students can only evaluate services that are ACTIVE<br>
                - Inactive services will not appear in the student evaluation form<br>
                - You can activate/deactivate services individually
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> EVSU Registrar Evaluation System</p>
        </div>
    </div>

    <script>
        // Add live status update for each toggle
        document.querySelectorAll('input[name="is_active"]').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const statusText = this.closest('.form-group').querySelector('.status-text');
                if (this.checked) {
                    statusText.textContent = 'ACTIVE';
                    statusText.style.color = '#28a745';
                } else {
                    statusText.textContent = 'INACTIVE';
                    statusText.style.color = '#dc3545';
                }
            });
        });
    </script>
</body>
</html>