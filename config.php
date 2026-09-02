<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'attendance_system');

// Site Configuration
define('SITE_NAME', 'Employee Attendance Management System');
define('SITE_URL', 'http://localhost/Attendance_mgmt/');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

// Time Configuration
date_default_timezone_set('Asia/Kolkata'); // Change as per your timezone

// Database Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Session Configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper Functions
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function check_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect('admin_login.php');
    }
}

function check_employee_login() {
    if (!isset($_SESSION['employee_logged_in']) || $_SESSION['employee_logged_in'] !== true) {
        redirect('employee_login.php');
    }
}

function log_activity($user_type, $user_id, $action, $description = '') {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    $action = sanitize_input($action);
    $description = sanitize_input($description);
    
    $query = "INSERT INTO system_logs (user_type, user_id, action, description, ip_address) 
              VALUES ('$user_type', $user_id, '$action', '$description', '$ip')";
    mysqli_query($conn, $query);
}

function get_attendance_status($employee_id, $date) {
    global $conn;
    $query = "SELECT status FROM attendance WHERE employee_id = $employee_id AND date = '$date'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['status'];
    }
    return 'absent';
}

function calculate_late_status($check_in_time, $shift_start) {
    $check_in = strtotime($check_in_time);
    $shift = strtotime($shift_start);
    $grace_period = 15 * 60; // 15 minutes grace period
    
    if ($check_in > ($shift + $grace_period)) {
        return 'late';
    }
    return 'present';
}
?>
