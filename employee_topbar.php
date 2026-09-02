<div class="topbar">
    <div class="topbar-left">
        <h4><?php echo date('l, F j, Y'); ?></h4>
    </div>
    
    <div class="topbar-right">
        <div class="user-info">
            <img src="<?php echo UPLOAD_URL . ($_SESSION['employee_profile'] ?? 'default_employee.png'); ?>" 
                 alt="Profile" class="user-avatar" 
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['employee_name']); ?>&background=1cc88a&color=fff'">
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['employee_name']; ?></div>
                <div class="user-role">Employee</div>
            </div>
        </div>
    </div>
</div>
