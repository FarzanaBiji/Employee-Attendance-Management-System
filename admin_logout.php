<?php
require_once 'config.php';
check_admin_login();

log_activity('admin', $_SESSION['admin_id'], 'Logout', 'Admin logged out');

// Destroy session
session_destroy();
redirect('admin_login.php');
?>
