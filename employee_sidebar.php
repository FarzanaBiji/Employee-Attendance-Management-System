<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-chart-line"></i> <?php echo SITE_NAME; ?></h3>
        <p>Employee Portal</p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="employee_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employee_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li>
            <a href="mark_attendance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'mark_attendance.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Mark Attendance</span>
            </a>
        </li>
        
        <li>
            <a href="employee_attendance_history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employee_attendance_history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Attendance History</span>
            </a>
        </li>
        
        <li>
            <a href="employee_leave_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employee_leave_management.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-times"></i>
                <span>Leave Management</span>
            </a>
        </li>
        
        <li>
            <a href="employee_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'employee_profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Profile Settings</span>
            </a>
        </li>
        
        <li>
            <a href="employee_logout.php" onclick="return confirm('Are you sure you want to logout?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
