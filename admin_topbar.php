<div class="topbar">
    <div class="topbar-left">
        <h4><?php echo date('l, F j, Y'); ?></h4>
    </div>
    
    <div class="topbar-right">
        <div class="user-info">
            <img src="<?php echo UPLOAD_URL . ($_SESSION['admin_profile'] ?? 'default_admin.png'); ?>" 
                 alt="Profile" class="user-avatar" 
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['admin_name']); ?>&background=4e73df&color=fff'">
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['admin_name']; ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </div>
</div>
