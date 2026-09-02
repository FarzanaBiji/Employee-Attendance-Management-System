<?php
require_once 'config.php';
check_employee_login();

log_activity('employee', $_SESSION['employee_id'], 'Logout', 'Employee logged out');

// Destroy session
session_destroy();
redirect('employee_login.php');
?>
