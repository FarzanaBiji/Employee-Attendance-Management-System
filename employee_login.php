<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['employee_logged_in']) && $_SESSION['employee_logged_in'] === true) {
    redirect('employee_dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $query = "SELECT * FROM employees WHERE email = '$email' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $employee = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $employee['password'])) {
                // Set session variables
                $_SESSION['employee_logged_in'] = true;
                $_SESSION['employee_id'] = $employee['employee_id'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['employee_email'] = $employee['email'];
                $_SESSION['employee_profile'] = $employee['profile_picture'];
                $_SESSION['employee_department'] = $employee['department_id'];
                
                // Log activity
                log_activity('employee', $employee['employee_id'], 'Login', 'Employee logged in successfully');
                
                redirect('employee_dashboard.php');
            } else {
                $error = 'Invalid email or password';
                log_activity('employee', 0, 'Failed Login', "Failed login attempt for email: $email");
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a3a8f 0%, #2c5aa0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
            opacity: 0.3;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            z-index: 1;
        }
        
        .login-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a3a8f 0%, #2c5aa0 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #4CAF50;
            background: white;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label i {
            margin-right: 5px;
            color: #1a3a8f;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1a3a8f;
            box-shadow: 0 0 0 3px rgba(26, 58, 143, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3d8b40;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .login-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        
        .login-footer p {
            margin: 10px 0;
            color: #666;
            font-size: 14px;
        }
        
        .login-footer a {
            color: #1a3a8f;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-box {
                margin: 20px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-tie"></i>
                <h2>Employee Portal</h2>
                <p>Secure Login Access</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Don't have an account? <a href="employee_register.php">Register here</a></p>
                <p>Admin? <a href="admin_login.php">Login here</a></p>
                <p><a href="index.html">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
