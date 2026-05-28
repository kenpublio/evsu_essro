<?php
// student/profile.php
require_once '../includes/config.php';
require_once '../includes/functions.php';



// Get current user data
$user_id = $_SESSION['user_id'];

// Get database connection
$conn = getDB();
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
// Initialize variables
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Profile
    if (isset($_POST['update_profile'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        
        if (empty($fullname)) {
            $error = "Full name is required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $fullname, $email, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['fullname'] = $fullname;
                $user['fullname'] = $fullname;
                $user['email'] = $email;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile. Please try again.";
            }
            $stmt->close();
        }
    }
    
    // Change Password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required!";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match!";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect!";
        } elseif (strlen($new_password) < 8) {
            $error = "New password must be at least 8 characters long!";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully! Please login again.";
                // Optional: Logout user to force re-login with new password
                // session_destroy();
                // header("Location: ../login.php");
                // exit();
            } else {
                $error = "Failed to change password. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: linear-gradient(to right, #8B0000, #A52A2A);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.5s ease;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Profile Layout */
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* Sidebar */
        .profile-sidebar {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #8B0000, #A52A2A);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .profile-info {
            margin-top: 20px;
            text-align: left;
        }
        
        .profile-info p {
            margin: 12px 0;
            color: #666;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .profile-info strong {
            color: #333;
            display: inline-block;
            min-width: 100px;
        }
        
        .student-id-badge {
            background: linear-gradient(135deg, #8B0000, #A52A2A);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        /* Main Content */
        .profile-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #8B0000;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fafafa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #8B0000;
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
        }
        
        .form-control:disabled {
            background: #e9ecef;
            cursor: not-allowed;
        }
        
        /* Buttons */
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #8B0000, #A52A2A);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #A52A2A, #8B0000);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
        }
        
        .tab {
            padding: 12px 25px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            color: #666;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #8B0000;
            border-bottom: 2px solid #8B0000;
        }
        
        .tab:hover:not(.active) {
            color: #333;
            background: #f9f9f9;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Info Box */
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .info-box i {
            color: #2196f3;
            margin-right: 10px;
        }
        
        /* Back Link */
        .back-link {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .back-link a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Enrollment Status */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-enrolled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-graduated {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-user-graduate"></i> Student Profile</h1>
                <p>Manage your account information and settings</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-container">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h2><?php echo htmlspecialchars(isset($user['fullname']) && !empty($user['fullname']) ? $user['fullname'] : $user['username']); ?></h2>
                <div class="student-id-badge">
                    <i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($user['student_id'] ?? 'N/A'); ?>
                </div>
                <p style="margin-top: 10px;">
                    <span class="status-badge status-<?php echo $user['enrollment_status'] ?? 'enrolled'; ?>">
                        <i class="fas fa-check-circle"></i> 
                        <?php echo ucfirst($user['enrollment_status'] ?? 'Enrolled'); ?>
                    </span>
                </p>
                
                <div class="profile-info">
                    <p><strong><i class="fas fa-id-card"></i> Student ID:</strong><br>
                       <span style="font-size: 1.1rem; font-weight: bold; color: #8B0000;">
                           <?php echo htmlspecialchars($user['student_id'] ?? 'N/A'); ?>
                       </span>
                    </p>
                    <p><strong><i class="fas fa-user"></i> Username:</strong><br>
                       <?php echo htmlspecialchars($user['username']); ?>
                    </p>
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong><br>
                       <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p><strong><i class="fas fa-calendar"></i> Joined:</strong><br>
                       <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                    </p>
                    <p><strong><i class="fas fa-clock"></i> Last Login:</strong><br>
                       <?php echo $user['last_login'] ? date('M d, Y h:i A', strtotime($user['last_login'])) : 'Never'; ?>
                    </p>
                </div>
                
                <div style="margin-top: 30px;">
                    <a href="../logout.php" class="btn btn-danger" style="width: 100%;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="profile-content">
                <!-- Tabs -->
                <div class="tabs">
                    <div class="tab active" onclick="switchTab('profile', event)">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </div>
                    <div class="tab" onclick="switchTab('password', event)">
                        <i class="fas fa-key"></i> Change Password
                    </div>
                </div>
                
                <!-- Edit Profile Tab -->
                <div id="profile-tab" class="tab-content active">
                    <h3 class="section-title"><i class="fas fa-user-edit"></i> Edit Profile Information</h3>
                    
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Your Student ID is your login credential and cannot be changed.
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="student_id">Student ID (Login Credential)</label>
                            <input type="text" id="student_id" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['student_id'] ?? 'N/A'); ?>" disabled>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                <i class="fas fa-lock"></i> Student ID is used for login and cannot be changed.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Username is for display purposes only.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <small>Only EVSU email accounts are allowed for registration.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="enrollment_status">Enrollment Status</label>
                            <input type="text" id="enrollment_status" class="form-control" 
                                   value="<?php echo ucfirst($user['enrollment_status'] ?? 'Enrolled'); ?>" disabled>
                            <small>Contact the Registrar's Office to update your enrollment status.</small>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div id="password-tab" class="tab-content">
                    <h3 class="section-title"><i class="fas fa-key"></i> Change Password</h3>
                    
                    <div class="info-box">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Password Requirements:</strong> Minimum 8 characters with letters and numbers.
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" 
                                   class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" 
                                   class="form-control" required>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Password must be at least 8 characters long.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="form-control" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
                
                <div class="back-link">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        function switchTab(tabName, event) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Activate clicked tab
            if (event && event.target) {
                event.target.classList.add('active');
            }
        }
        
        // Password confirmation check
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (newPassword.value !== this.value) {
                    this.setCustomValidity('Passwords do not match!');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                if (confirmPassword && confirmPassword.value !== this.value) {
                    confirmPassword.setCustomValidity('Passwords do not match!');
                } else if (confirmPassword) {
                    confirmPassword.setCustomValidity('');
                }
            });
        }
    </script>
</body>
</html>