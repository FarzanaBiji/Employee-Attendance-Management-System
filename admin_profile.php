<?php
require_once 'config.php';
check_admin_login();

$admin_id = $_SESSION['admin_id'];
$success = '';
$error = '';

// Get admin details
$query = "SELECT * FROM admins WHERE admin_id = $admin_id";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'update_profile') {
        $name = sanitize_input($_POST['name']);
        $phone = sanitize_input($_POST['phone']);
        
        $query = "UPDATE admins SET name='$name', phone='$phone' WHERE admin_id=$admin_id";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Profile updated successfully!';
            $_SESSION['admin_name'] = $name;
            log_activity('admin', $admin_id, 'Update Profile', 'Updated profile information');
            // Refresh data
            header("Refresh:0");
        } else {
            $error = 'Error updating profile';
        }
    }
    
    elseif ($action == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $admin['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE admins SET password='$hashed_password' WHERE admin_id=$admin_id";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Password changed successfully!';
                log_activity('admin', $admin_id, 'Change Password', 'Password changed');
            } else {
                $error = 'Error changing password';
            }
        }
    }
}

// Refresh admin data
$result = mysqli_query($conn, "SELECT * FROM admins WHERE admin_id = $admin_id");
$admin = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/admin_topbar.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Profile Settings</h1>
                    <p class="breadcrumb">Home / Profile Settings</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Profile Information -->
                <div class="form-container mb-20">
                    <h3 class="mb-20">Profile Information</h3>
                    
                    <div class="flex gap-20 align-center mb-20">
                        <img src="<?php echo UPLOAD_URL . $admin['profile_picture']; ?>" 
                             alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($admin['name']); ?>&size=100&background=4e73df&color=fff'">
                        <div>
                            <h4><?php echo $admin['name']; ?></h4>
                            <p>Administrator</p>
                            <p><span class="badge badge-primary"><?php echo $admin['email']; ?></span></p>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo $admin['name']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" 
                                       value="<?php echo $admin['email']; ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?php echo $admin['phone']; ?>" placeholder="Enter phone number">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="form-container">
                    <h3 class="mb-20">Change Password</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Password *</label>
                                <input type="password" name="new_password" class="form-control" 
                                       minlength="6" required>
                                <small>Password must be at least 6 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password *</label>
                                <input type="password" name="confirm_password" class="form-control" 
                                       minlength="6" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
