<?php
session_start();  
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Debug - remove after testing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST received: " . print_r($_POST, true));
}
// Initialize Functions class
$functions = new Functions();

// Get current user
$user_id = $_SESSION['user_id'];
$user = $functions->getUserById($user_id);

// Get database connection
$conn = getDB();

// Get service types
$services = [];
$result = $conn->query("SELECT * FROM service_types WHERE is_active = 1 ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[$row['id']] = $row;
    }
}

// Get service type from URL - default to first service if none selected
$selected_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

// If no service selected, get the first active service
if ($selected_service_id === 0 && !empty($services)) {
    $first_service = reset($services);
    $selected_service_id = $first_service['id'];
    header("Location: questions.php?service_id=" . $selected_service_id);
    exit();
}

// Ensure service_type_id column exists
$col_check = $conn->query("SHOW COLUMNS FROM survey_questions LIKE 'service_type_id'");
if ($col_check->num_rows == 0) {
    $conn->query("ALTER TABLE survey_questions ADD COLUMN service_type_id INT NULL AFTER office_id");
    $conn->query("ALTER TABLE survey_questions ADD INDEX idx_service_type_id (service_type_id)");
}

// Handle form submissions
$message = '';
$error = '';

// Function to get default questions for a service
function getDefaultQuestions($service_name) {
    $common_questions = [
        1 => ['text' => 'How satisfied are you with the processing time?', 'category' => 'service'],
        2 => ['text' => 'How helpful and courteous were the staff?', 'category' => 'staff'],
        3 => ['text' => 'How clear were the instructions and requirements?', 'category' => 'information'],
        4 => ['text' => 'How would you rate the accuracy of your document/transaction?', 'category' => 'quality'],
        5 => ['text' => 'How convenient was the overall process?', 'category' => 'convenience'],
        6 => ['text' => 'How would you rate the waiting time?', 'category' => 'waiting'],
        7 => ['text' => 'How organized was the service delivery?', 'category' => 'organization'],
        8 => ['text' => 'How likely are you to recommend this service to others?', 'category' => 'recommendation'],
        9 => ['text' => 'How would you rate the cleanliness of the office?', 'category' => 'facility'],
        10 => ['text' => 'Overall, how satisfied are you with this service?', 'category' => 'overall']
    ];
    
    // Service-specific customizations
    $customizations = [
        'TOR' => [
            4 => 'How accurate was your Transcript of Records?',
            5 => 'How easy was the TOR request process?',
            6 => 'How long did you wait for your TOR?',
            10 => 'Overall satisfaction with TOR request service?'
        ],
        'Certification' => [
            4 => 'How accurate was your certification document?',
            5 => 'How easy was the certification request process?',
            6 => 'How long did you wait for your certification?',
            10 => 'Overall satisfaction with certification service?'
        ],
        'Enrollment' => [
            4 => 'Were your subjects encoded correctly?',
            5 => 'How smooth was the enrollment process?',
            6 => 'How long did the enrollment take?',
            10 => 'Overall satisfaction with enrollment process?'
        ],
        'Diploma' => [
            4 => 'How accurate was your diploma?',
            5 => 'How easy was the diploma request process?',
            6 => 'How long did you wait for your diploma?',
            10 => 'Overall satisfaction with diploma service?'
        ],
        'Authentication' => [
            4 => 'How accurate was your document authentication?',
            5 => 'How easy was the authentication process?',
            6 => 'How long did you wait for authentication?',
            10 => 'Overall satisfaction with authentication service?'
        ]
    ];
    
    if (isset($customizations[$service_name])) {
        foreach ($customizations[$service_name] as $key => $custom_text) {
            $common_questions[$key]['text'] = $custom_text;
        }
    }
    
    return $common_questions;
}

// Add default questions for a service
if (isset($_GET['add_default']) && isset($_GET['service_id'])) {
    $service_id = (int)$_GET['service_id'];
    $service_name = $services[$service_id]['name'] ?? '';
    
    if ($service_name) {
        $default_questions = getDefaultQuestions($service_name);
        
        // Check if questions already exist for this service
        $check = $conn->prepare("SELECT COUNT(*) as count FROM survey_questions WHERE service_type_id = ?");
        $check->bind_param("i", $service_id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc()['count'];
        
        if ($existing == 0) {
            $inserted = 0;
            foreach ($default_questions as $order => $q) {
                $stmt = $conn->prepare("INSERT INTO survey_questions (office_id, service_type_id, question_text, display_order, category, is_active) VALUES (20, ?, ?, ?, ?, 1)");
                $stmt->bind_param("issi", $service_id, $q['text'], $order, $q['category']);
                if ($stmt->execute()) {
                    $inserted++;
                }
            }
            $message = "$inserted default questions added for {$service_name} service!";
            
            // Log action
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'add_default_questions', '$ip', '$agent')");
        } else {
            $error = "Questions already exist for {$service_name}. Delete existing questions first if you want to add defaults.";
        }
    }
}

// Add new question
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $question_text = trim($_POST['question_text'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $category = trim($_POST['category'] ?? 'general');
    $service_type_id = isset($_POST['service_type_id']) ? (int)$_POST['service_type_id'] : $selected_service_id;
    
    if (empty($question_text)) {
        $error = 'Question text is required';
    } else {
        $stmt = $conn->prepare("INSERT INTO survey_questions (office_id, service_type_id, question_text, display_order, category, is_active) VALUES (20, ?, ?, ?, ?, 1)");
        $stmt->bind_param("isss", $service_type_id, $question_text, $display_order, $category);
        
        if ($stmt->execute()) {
            $message = 'Question added successfully for ' . htmlspecialchars($services[$service_type_id]['name']);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'add_question', '$ip', '$agent')");
        } else {
            $error = 'Failed to add question: ' . $conn->error;
        }
    }
}

// Edit question
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $question_id = (int)($_POST['question_id'] ?? 0);
    $question_text = trim($_POST['question_text'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $category = trim($_POST['category'] ?? 'general');
    
    if (empty($question_text)) {
        $error = 'Question text is required';
    } else {
        $stmt = $conn->prepare("UPDATE survey_questions SET question_text = ?, display_order = ?, category = ? WHERE id = ? AND office_id = 20 AND service_type_id = ?");
        $stmt->bind_param("sisii", $question_text, $display_order, $category, $question_id, $selected_service_id);
        
        if ($stmt->execute()) {
            $message = 'Question updated successfully';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'edit_question', '$ip', '$agent')");
        } else {
            $error = 'Failed to update question: ' . $conn->error;
        }
    }
}

// Toggle question status
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $question_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT is_active FROM survey_questions WHERE id = ? AND office_id = 20 AND service_type_id = ?");
    $stmt->bind_param("ii", $question_id, $selected_service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    
    if ($question) {
        $new_status = $question['is_active'] ? 0 : 1;
        $update = $conn->prepare("UPDATE survey_questions SET is_active = ? WHERE id = ? AND office_id = 20 AND service_type_id = ?");
        $update->bind_param("iii", $new_status, $question_id, $selected_service_id);
        
        if ($update->execute()) {
            $message = $new_status ? 'Question activated' : 'Question deactivated';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $action = $new_status ? 'activate_question' : 'deactivate_question';
            $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, '$action', '$ip', '$agent')");
        } else {
            $error = 'Failed to update question status';
        }
    }
}

// Delete question
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $question_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM survey_questions WHERE id = ? AND office_id = 20 AND service_type_id = ?");
    $stmt->bind_param("ii", $question_id, $selected_service_id);
    
    if ($stmt->execute()) {
        $message = 'Question deleted successfully';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'delete_question', '$ip', '$agent')");
    } else {
        $error = 'Failed to delete question';
    }
}

// Delete all questions for a service
if (isset($_GET['delete_all']) && isset($_GET['service_id'])) {
    $service_id = (int)$_GET['service_id'];
    $service_name = $services[$service_id]['name'] ?? '';
    
    $stmt = $conn->prepare("DELETE FROM survey_questions WHERE office_id = 20 AND service_type_id = ?");
    $stmt->bind_param("i", $service_id);
    
    if ($stmt->execute()) {
        $message = "All questions deleted for {$service_name} service";
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'delete_all_questions', '$ip', '$agent')");
    } else {
        $error = "Failed to delete questions";
    }
}

// Get questions for selected service only
$sql = "SELECT * FROM survey_questions WHERE office_id = 20 AND service_type_id = ? ORDER BY display_order, id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $selected_service_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Survey Questions - ' . ($services[$selected_service_id]['name'] ?? 'Registrar');
?>

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
            --warning-orange: #fd7e14;
            --info-blue: #17a2b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--evsu-gray);
            min-height: 100vh;
        }

        /* Header */
        .evsu-header {
            background: linear-gradient(135deg, var(--evsu-red) 0%, #B22222 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
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
            gap: 20px;
        }

        .evsu-logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--evsu-red);
            font-weight: bold;
        }

        .title-section h1 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .title-section .subtitle {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--evsu-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--evsu-red);
            font-weight: bold;
        }

        .user-details {
            line-height: 1.4;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .user-actions {
            display: flex;
            gap: 10px;
            margin-top: 3px;
        }

        .user-actions a {
            color: white;
            text-decoration: none;
            font-size: 0.75rem;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .user-actions a:hover {
            opacity: 1;
            color: var(--evsu-gold);
        }

        /* Layout */
        .main-container {
            display: flex;
            max-width: 1400px;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px 0;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .sidebar-header {
            padding: 0 20px 15px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 15px;
        }

        .sidebar-header h3 {
            color: var(--evsu-red);
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-header h3 i {
            margin-right: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: #fff5f5;
            color: var(--evsu-red);
            border-left-color: var(--evsu-red);
        }

        .nav-item.active {
            background: #fff0f0;
            color: var(--evsu-red);
            border-left-color: var(--evsu-red);
            font-weight: 500;
        }

        .nav-icon {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
            text-align: center;
        }

        /* Main Content */
        .content {
            flex: 1;
        }

        .content-header {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header h2 {
            color: var(--evsu-dark);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .content-header h2 i {
            color: var(--evsu-red);
            margin-right: 10px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--evsu-red);
            color: white;
        }

        .btn-primary:hover {
            background: #6b0000;
        }

        .btn-success {
            background: var(--success-green);
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-outline {
            background: white;
            border: 1px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            background: #f8f9fa;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: var(--warning-orange);
            color: white;
        }

        .btn-warning:hover {
            background: #e66b00;
        }

        /* Service Selector */
        .service-selector {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .service-selector h3 {
            color: var(--evsu-dark);
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .service-selector h3 i {
            color: var(--evsu-red);
        }

        .service-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .service-btn {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 40px;
            padding: 8px 20px;
            text-decoration: none;
            color: #666;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .service-btn:hover {
            border-color: var(--evsu-red);
            background: #fff5f5;
            color: var(--evsu-red);
        }

        .service-btn.active {
            background: var(--evsu-red);
            border-color: var(--evsu-red);
            color: white;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Info Card */
        .info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-card h3 {
            color: var(--evsu-dark);
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card h3 i {
            color: var(--evsu-red);
        }

        .registrar-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            display: inline-block;
        }

        .registrar-badge i {
            margin-right: 5px;
        }

        /* Questions Grid */
        .questions-grid {
            display: grid;
            gap: 15px;
        }

        .question-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
            position: relative;
        }

        .question-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: var(--evsu-red);
        }

        .question-item.inactive {
            opacity: 0.7;
            background: #f8f9fa;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .question-order {
            background: var(--evsu-red);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .question-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .action-btn.edit {
            background: var(--info-blue);
        }

        .action-btn.edit:hover {
            background: #138496;
        }

        .action-btn.toggle {
            background: var(--warning-orange);
        }

        .action-btn.toggle:hover {
            background: #e66b00;
        }

        .action-btn.delete {
            background: #dc3545;
        }

        .action-btn.delete:hover {
            background: #c82333;
        }

        .question-text {
            font-size: 1rem;
            font-weight: 500;
            color: var(--evsu-dark);
            margin-bottom: 10px;
            padding-right: 100px;
        }

        .question-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .category-badge {
            background: #e9ecef;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #495057;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            color: var(--evsu-dark);
        }

        .modal-header h3 i {
            color: var(--evsu-red);
            margin-right: 8px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 2px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--evsu-red);
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .form-text {
            font-size: 0.8rem;
            color: #999;
            margin-top: 5px;
        }

        /* Footer */
        .footer {
            background: var(--evsu-dark);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }

        .footer a {
            color: #ddd;
            text-decoration: none;
            margin: 0 15px;
            font-size: 0.9rem;
        }

        .footer a:hover {
            color: var(--evsu-gold);
        }

        .copyright {
            font-size: 0.85rem;
            color: #777;
            margin-top: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: static;
            }
            
            .question-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .question-text {
                padding-right: 0;
            }
            
            .service-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="evsu-header">
        <div class="header-container">
            <div class="logo-section">
                <div class="evsu-logo">
                    <i class="fas fa-university"></i>
                </div>
                <div class="title-section">
                    <h1>EVSU - Registrar's Office</h1>
                    <div class="subtitle">Survey Questions Manager</div>
                </div>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['fullname'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name">
                        <?php echo htmlspecialchars($user['fullname'] ?? $user['username'] ?? 'Admin'); ?>
                    </div>
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
            <div class="sidebar-header">
                <h3><i class="fas fa-regular fa-building"></i> REGISTRAR MENU</h3>
            </div>

            <a href="index.php" class="nav-item">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="evaluations.php" class="nav-item">
                <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>All Evaluations</span>
            </a>
            <a href="questions.php" class="nav-item active">
                <span class="nav-icon"><i class="fas fa-question-circle"></i></span>
                <span>Survey Questions</span>
            </a>
            <a href="students.php" class="nav-item">
                <span class="nav-icon"><i class="fas fa-user-graduate"></i></span>
                <span>Student List</span>
            </a>
            <a href="report.php" class="nav-item">
                <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                <span>Reports</span>
            </a>
            <a href="logs.php" class="nav-item">
                <span class="nav-icon"><i class="fas fa-history"></i></span>
                <span>Activity Logs</span>
            </a>
        </aside>

        <main class="content">
            <div class="content-header">
                <h2>
                    <i class="fas fa-question-circle"></i>
                    Survey Questions - <?php echo htmlspecialchars($services[$selected_service_id]['name'] ?? 'Service'); ?>
                </h2>
                <div class="header-actions">
                    <?php if (count($questions) == 0): ?>
                        <a href="?add_default=1&service_id=<?php echo $selected_service_id; ?>" class="btn btn-warning" onclick="return confirm('Add 10 default questions for this service?')">
                            <i class="fas fa-database"></i> Add 10 Default Questions
                        </a>
                    <?php endif; ?>
                    <?php if (count($questions) > 0): ?>
                        <a href="?delete_all=1&service_id=<?php echo $selected_service_id; ?>" class="btn btn-danger" onclick="return confirm('Delete ALL questions for this service? This cannot be undone!')">
                            <i class="fas fa-trash-alt"></i> Delete All
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-success" onclick="showAddModal()">
                        <i class="fas fa-plus-circle"></i> Add Question
                    </button>
                </div>
            </div>

            <!-- Service Type Selection -->
            <div class="service-selector">
                <h3>
                    <i class="fas fa-tag"></i>
                    Select Service Type to Manage Questions
                </h3>
                <p style="color: #666; margin-bottom: 15px;">Click on a service below to manage its specific survey questions:</p>
                
                <div class="service-buttons">
                    <?php foreach ($services as $service): ?>
                        <a href="?service_id=<?php echo $service['id']; ?>" class="service-btn <?php echo $selected_service_id == $service['id'] ? 'active' : ''; ?>">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($service['name']); ?>
                            <?php 
                                $count = $conn->query("SELECT COUNT(*) as c FROM survey_questions WHERE service_type_id = {$service['id']}")->fetch_assoc()['c'];
                                echo "<span style='margin-left: 5px; font-size: 0.7rem;'>($count)</span>";
                            ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="info-card">
                <h3>
                    <i class="fas fa-building"></i>
                    <?php echo htmlspecialchars($services[$selected_service_id]['name'] ?? 'Service'); ?> - Survey Questions
                </h3>
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <span class="registrar-badge">
                        <i class="fas fa-clipboard-list"></i> Total Questions: <?php echo count($questions); ?>/10
                    </span>
                    <span class="registrar-badge" style="background: var(--success-green);">
                        <i class="fas fa-check-circle"></i> Active: <?php echo count(array_filter($questions, function($q) { return $q['is_active'] == 1; })); ?>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <h3>
                    <i class="fas fa-list"></i>
                    Survey Questions for <?php echo htmlspecialchars($services[$selected_service_id]['name'] ?? 'Service'); ?>
                </h3>

                <?php if (empty($questions)): ?>
                    <div style="text-align: center; padding: 50px 20px; color: #999;">
                        <i class="fas fa-question-circle" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No questions yet for <?php echo htmlspecialchars($services[$selected_service_id]['name'] ?? 'this service'); ?>.</p>
                        <p style="margin-top: 10px;">Click <strong>"Add 10 Default Questions"</strong> to add the default set of 10 questions, or add them one by one.</p>
                    </div>
                <?php else: ?>
                    <div class="questions-grid">
                        <?php foreach ($questions as $q): ?>
                            <div class="question-item <?php echo $q['is_active'] ? '' : 'inactive'; ?>">
                                <div class="question-header">
                                    <span class="question-order"><?php echo $q['display_order'] ?? $q['id']; ?></span>
                                    <div class="question-actions">
                                        <button class="action-btn edit" onclick='showEditModal(<?php echo json_encode($q); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?toggle=1&id=<?php echo $q['id']; ?>&service_id=<?php echo $selected_service_id; ?>" class="action-btn toggle" onclick="return confirm('Toggle question status?')">
                                            <i class="fas fa-<?php echo $q['is_active'] ? 'ban' : 'check'; ?>"></i>
                                        </a>
                                        <a href="?delete=1&id=<?php echo $q['id']; ?>&service_id=<?php echo $selected_service_id; ?>" class="action-btn delete" onclick="return confirm('Delete this question?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="question-text">
                                    <?php echo htmlspecialchars($q['question_text']); ?>
                                </div>
                                
                                <div class="question-meta">
                                    <span class="category-badge">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($q['category'] ?? 'General'); ?>
                                    </span>
                                    <span class="status-badge <?php echo $q['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <i class="fas fa-<?php echo $q['is_active'] ? 'check-circle' : 'minus-circle'; ?>"></i>
                                        <?php echo $q['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <span style="color: #999; font-size: 0.8rem;">
                                        <i class="far fa-clock"></i> 
                                        Order: <?php echo $q['display_order'] ?? 'N/A'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="info-card" style="background: #e3f2fd; border-left: 4px solid #2196f3;">
                <h3 style="color: #1976d2;">
                    <i class="fas fa-lightbulb"></i>
                    10 Default Questions for Each Service
                </h3>
                <ul style="margin-left: 20px; color: #0d47a1; line-height: 1.8;">
                    <li>1. How satisfied are you with the processing time?</li>
                    <li>2. How helpful and courteous were the staff?</li>
                    <li>3. How clear were the instructions and requirements?</li>
                    <li>4. How would you rate the accuracy of your document/transaction?</li>
                    <li>5. How convenient was the overall process?</li>
                    <li>6. How would you rate the waiting time?</li>
                    <li>7. How organized was the service delivery?</li>
                    <li>8. How likely are you to recommend this service to others?</li>
                    <li>9. How would you rate the cleanliness of the office?</li>
                    <li>10. Overall, how satisfied are you with this service?</li>
                </ul>
            </div>
        </main>
    </div>

    <!-- Add Question Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add Question for <?php echo htmlspecialchars($services[$selected_service_id]['name'] ?? 'Service'); ?></h3>
                <button class="close-modal" onclick="hideAddModal()">&times;</button>
            </div>
            <form method="POST" action="?service_id=<?php echo $selected_service_id; ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea name="question_text" class="form-control" required placeholder="Enter your survey question..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Display Order (1-10)</label>
                        <input type="number" name="display_order" class="form-control" value="<?php echo count($questions) + 1; ?>" min="1" max="10">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="service">Service Quality</option>
                            <option value="staff">Staff Performance</option>
                            <option value="information">Information Clarity</option>
                            <option value="facility">Office Facility</option>
                            <option value="waiting">Waiting Time</option>
                            <option value="overall">Overall Experience</option>
                            <option value="general" selected>General</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
    <label>Service Type</label>
    <select name="service_type_id" class="form-control" required>
        <?php foreach ($services as $service): ?>
            <option value="<?php echo $service['id']; ?>" <?php echo $selected_service_id == $service['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($service['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="form-text">Select which service this question belongs to</div>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Question</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Question</h3>
                <button class="close-modal" onclick="hideEditModal()">&times;</button>
            </div>
            <form method="POST" action="?service_id=<?php echo $selected_service_id; ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="question_id" id="edit_id">
                    
                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea name="question_text" id="edit_text" class="form-control" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="display_order" id="edit_order" class="form-control" min="1" max="10">
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="edit_category" class="form-control">
                            <option value="service">Service Quality</option>
                            <option value="staff">Staff Performance</option>
                            <option value="information">Information Clarity</option>
                            <option value="facility">Office Facility</option>
                            <option value="waiting">Waiting Time</option>
                            <option value="overall">Overall Experience</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Question</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div>
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="questions.php"><i class="fas fa-question-circle"></i> Questions</a>
            <a href="../logout.php" onclick="return confirm('Logout?');">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <div class="copyright">
            <i class="fas fa-copyright"></i> <?php echo date('Y'); ?> EVSU Registrar Evaluation System
        </div>
    </footer>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.add('show');
        }
        
        function hideAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }
        
        function showEditModal(question) {
            document.getElementById('edit_id').value = question.id;
            document.getElementById('edit_text').value = question.question_text;
            document.getElementById('edit_order').value = question.display_order || question.id;
            document.getElementById('edit_category').value = question.category || 'general';
            document.getElementById('editModal').classList.add('show');
        }
        
        function hideEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                hideAddModal();
            }
            if (event.target === editModal) {
                hideEditModal();
            }
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideAddModal();
                hideEditModal();
            }
        });
    </script>
</body>
</html>