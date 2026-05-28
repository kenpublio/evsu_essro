<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';


if (!isset($conn)) {
    die("Database connection not established. Please check the config file.");
}

$auth = new Auth();

// Check if user is admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success = false;
$error = '';

// EVSU Email Validation Function
function isValidEVSUEmail($email) {
    $allowed_domains = [
        '@evsu.edu.ph',
        '@students.evsu.edu.ph',
        '@evsu-occ.edu.ph'
    ];
    
    foreach ($allowed_domains as $domain) {
        if (strpos($email, $domain) !== false) {
            return true;
        }
    }
    return false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $role = 'student'; // Force role to student only
    
    // Validation
    $errors = [];
    
    // Basic validation
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($fullname)) $errors[] = "Full name is required";
    if (empty($student_id)) $errors[] = "Student ID is required (used for login)";
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    elseif (!isValidEVSUEmail($email)) $errors[] = "Only EVSU email accounts (@evsu.edu.ph) are allowed!";
    
    // Student ID format validation (YYYY-XXXXX)
    if (!empty($student_id) && !preg_match('/^\d{4}-\d{5}$/', $student_id)) {
        $errors[] = "Student ID must be in format: YYYY-XXXXX (e.g., 2023-12345)";
    }
    
    // Password length validation
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    // Username format validation (optional, for display only)
    if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters (letters, numbers, underscore only)";
    }
    
    // If no errors, proceed
    if (empty($errors)) {
        try {
            // Check if student_id exists (used for login)
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
            $check_stmt->bind_param("s", $student_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "Student ID already exists! This Student ID is used for login.";
            } else {
                $check_stmt->close();
                
                // Check for duplicate username or email
                $check2_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $check2_stmt->bind_param("ss", $username, $email);
                $check2_stmt->execute();
                $check2_stmt->store_result();
                
                if ($check2_stmt->num_rows > 0) {
                    $error = "Username or Email already exists";
                    $check2_stmt->close();
                } else {
                    $check2_stmt->close();
                    
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new student
                    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, fullname, student_id, email, role, enrollment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'enrolled', NOW())");
                    $insert_stmt->bind_param("ssssss", $username, $hashed_password, $fullname, $student_id, $email, $role);
                    
                    if ($insert_stmt->execute()) {
                        $success = true;
                        
                        // Log the action
                        $user_id = $_SESSION['user_id'];
                        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                        $conn->query("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES ($user_id, 'add_student', '$ip', '$agent')");
                        
                        // Clear form data on success
                        $_POST = array();
                    } else {
                        $error = "Failed to create user: " . $conn->error;
                    }
                    $insert_stmt->close();
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - EVSU Evaluation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: #8B0000;
            color: white;
            padding: 15px 20px;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            height: 50px;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .title-box {
            background: linear-gradient(135deg, #8B0000 0%, #6b0000 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .title-box h1 {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .title-box h1 i {
            color: #FFC107;
            font-size: 32px;
        }
        
        .title-box .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .required::after {
            content: " *";
            color: red;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .login-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .login-info i {
            font-size: 24px;
            color: #856404;
        }
        
        .login-info strong {
            color: #856404;
        }
        
        .student-fields {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            border: 1px solid #e0e0e0;
        }
        
        .student-fields h3 {
            color: #8B0000;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #8B0000 0%, #6b0000 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #6b0000 0%, #8B0000 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            color: black;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: var(--evsu-red);
            color: white;
            transform: translateX(-5px);
            border-color: var(--evsu-red);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .info-badge {
            background: #e3f2fd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1565c0;
            border-left: 4px solid #2196f3;
        }
        
        .info-badge i {
            font-size: 22px;
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
        .form-hint {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }
        
        .form-hint i {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .card {
                padding: 20px;
            }
            .btn-group {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="../images/EVSU_Official_Logo.png" alt="EVSU Logo">
                <div>
                    <h2>EVSU-OCC</h2>
                    <p style="font-size: 14px; opacity: 0.9;">Administrator Panel</p>
                </div>
            </div>
            <div>
            <a href="students.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="title-box">
                <h1>
                    <i class="fas fa-user-graduate"></i> Add New Student
                </h1>
                <div class="subtitle">Create a new student account for the evaluation system</div>
            </div>
            
            <!-- Important Login Info -->
            <div class="login-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Important:</strong> Student ID is used for login, not username.<br>
                    Students will log in using their <strong>Student ID</strong> (format: YYYY-XXXXX) and password.
                </div>
            </div>
            
            <div class="info-badge">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong>Email Requirement:</strong> Only EVSU email accounts are allowed (@evsu.edu.ph)
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <div>
                        <strong>Success!</strong> Student account has been created successfully.<br>
                        The student can now login using their <strong>Student ID</strong> and password.
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                    <div>
                        <strong>Error!</strong><br> <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="userForm">
                <!-- Basic Information -->
                <div class="student-fields">
                    <h3>
                        <i class="fas fa-id-card"></i> Login Credentials
                    </h3>
                    
                    <div class="form-group">
                        <label for="student_id" class="required">Student ID (Used for Login)</label>
                        <input type="text" name="student_id" id="student_id" required
                               placeholder="e.g., 2023-12345" value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>"
                               pattern="\d{4}-\d{5}" title="Format: YYYY-XXXXX">
                        <span class="form-hint"><i class="fas fa-key"></i> This will be used as login credential. Format: YYYY-XXXXX</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <input type="password" name="password" id="password" required 
                               placeholder="Enter password (minimum 8 characters)">
                        <span class="form-hint"><i class="fas fa-lock"></i> Minimum 8 characters</span>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="student-fields">
                    <h3>
                        <i class="fas fa-user-circle"></i> Personal Information
                    </h3>
                    
                    <div class="form-group">
                        <label for="fullname" class="required">Full Name</label>
                        <input type="text" name="fullname" id="fullname" required 
                               placeholder="Enter student's complete name" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="required">Username (Display Only)</label>
                        <input type="text" name="username" id="username" required 
                               placeholder="Enter unique username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               pattern="[a-zA-Z0-9_]{3,20}" title="3-20 characters, letters, numbers, underscore only">
                        <span class="form-hint"><i class="fas fa-user"></i> Username is for display purposes only. Login uses Student ID.</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" name="email" id="email" required
                               placeholder="student@evsu.edu.ph" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <span class="form-hint"><i class="fas fa-envelope"></i> Only EVSU email accounts allowed (@evsu.edu.ph)</span>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Create Student Account
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-redo"></i> Reset Form
                    </button>
                    <a href="students.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students List
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById('userForm').reset();
        }
        
        // Auto-hide success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = 'opacity 0.5s';
                    successAlert.style.opacity = '0';
                    setTimeout(() => {
                        if (successAlert.parentNode) successAlert.remove();
                    }, 500);
                }, 5000);
            }
            
            // Student ID format validation
            const studentIdField = document.getElementById('student_id');
            if (studentIdField) {
                studentIdField.addEventListener('input', function() {
                    const value = this.value;
                    if (value && !/^\d{4}-\d{5}$/.test(value)) {
                        this.style.borderColor = '#ffc107';
                    } else {
                        this.style.borderColor = '#ddd';
                    }
                });
            }
        });
    </script>
</body>
</html>