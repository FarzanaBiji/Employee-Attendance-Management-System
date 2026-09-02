<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-chart-line"></i> <?php echo SITE_NAME; ?></h3>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li>
            <a href="employee_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employee_management.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Manage Employees</span>
            </a>
        </li>
        
        <li>
            <a href="daily_attendance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'daily_attendance.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Daily Attendance</span>
            </a>
        </li>
        
        <li>
            <a href="attendance_reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'attendance_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Attendance Reports</span>
            </a>
        </li>
        
        <li>
            <a href="admin_leave_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_leave_management.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-times"></i>
                <span>Leave Management</span>
            </a>
        </li>
        
        <li>
            <a href="department_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'department_management.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Departments</span>
            </a>
        </li>
        
        <li>
            <a href="admin_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Profile Settings</span>
            </a>
        </li>
        
        <li>
            <a href="admin_logout.php" onclick="return confirm('Are you sure you want to logout?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
