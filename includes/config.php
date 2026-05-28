<?php

declare(strict_types=1);

// ============================================
// DATABASE CONFIGURATION
// ============================================

// Auto-detect environment
$is_local = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1');

if ($is_local) {
    // Local XAMPP configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'evsu_evaluation');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Hostinger production configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u656591888_eva_evsu_db');
    define('DB_USER', 'u656591888_eva');
    define('DB_PASS', 'Kenrepollo_12');
}

define('DB_CHARSET', 'utf8mb4');

// ============================================
// APPLICATION CONFIGURATION
// ============================================

// Application timezone
define('APP_TIMEZONE', 'Asia/Manila');
date_default_timezone_set(APP_TIMEZONE);

// EVSU Theme Colors
define('COLOR_PRIMARY', '#8B0000');    // EVSU Red/Maroon
define('COLOR_ACCENT', '#FFD700');     // EVSU Gold/Yellow
define('COLOR_LIGHT', '#ffffff');      // White
define('COLOR_DARK', '#1a1a1a');       // Dark text

// ============================================
// FILE UPLOAD CONFIGURATION
// ============================================

define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('PROFILE_PICTURES_DIR', UPLOAD_DIR . 'profile_pictures/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);

// ============================================
// BACKUP CONFIGURATION
// ============================================

define('BACKUP_DIR', dirname(__DIR__) . '/backups/');
define('MAX_BACKUPS', 30);

// ============================================
// SURVEY CONFIGURATION
// ============================================

define('DEFAULT_QUESTIONS_PER_SERVICE', 10);
define('RATING_SCALE_MIN', 1);
define('RATING_SCALE_MAX', 5);

// ============================================
// EMAIL CONFIGURATION (Optional)
// ============================================

define('MAIL_FROM_EMAIL', 'noreply@evsu.edu.ph');
define('MAIL_FROM_NAME', 'EVSU Registrar Evaluation System');
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USER', 'your_email@gmail.com');
define('MAIL_SMTP_PASS', 'your_app_password');
define('MAIL_SMTP_SECURE', 'tls');

// ============================================
// DATABASE CONNECTION FUNCTION (MySQLi)
// ============================================

function db_connect() {
    static $conn = null;
    
    if ($conn instanceof mysqli) {
        return $conn;
    }
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset(DB_CHARSET);
    
    // Set timezone
    $tz = new DateTimeZone(APP_TIMEZONE);
    $now = new DateTime('now', $tz);
    $offset = $now->format('P');
    $conn->query("SET time_zone = '{$offset}'");
    
    return $conn;
}

// Global connection variable for backward compatibility
$conn = db_connect();

function getDB() {
    global $conn;
    return $conn;
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function isLocal() {
    return ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1');
}

function base_url($path = '') {
    $is_local = isLocal();
    
    if ($is_local) {
        $base = 'http://localhost/evsuoc_essr';
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'lightgray-hippopotamus-757168.hostingersite.com';
        $base = $scheme . '://' . $host;
    }
    
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function redirect($path) {
    header("Location: " . base_url($path));
    exit();
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>