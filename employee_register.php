<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['employee_logged_in']) && $_SESSION['employee_logged_in'] === true) {
    redirect('employee_dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_id = intval($_POST['department_id']);
    $designation = sanitize_input($_POST['designation']);
    $joining_date = sanitize_input($_POST['joining_date']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_query = "SELECT * FROM employees WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email address already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new employee
            $insert_query = "INSERT INTO employees (name, email, password, phone, department_id, designation, joining_date, status) 
                           VALUES ('$name', '$email', '$hashed_password', '$phone', $department_id, '$designation', '$joining_date', 'active')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Registration successful! You can now login.';
                
                // Log activity
                $employee_id = mysqli_insert_id($conn);
                log_activity('employee', $employee_id, 'Registration', 'New employee registered successfully');
                
                // Redirect after 2 seconds
                header("refresh:2;url=employee_login.php");
            } else {
                $error = 'Registration failed. Please try again. Error: ' . mysqli_error($conn);
            }
        }
    }
}

// Get departments for dropdown
$dept_query = "SELECT * FROM departments ORDER BY department_name";
$dept_result = mysqli_query($conn, $dept_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration - <?php echo SITE_NAME; ?></title>
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
            padding: 40px 20px;
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
        
        .register-container {
            width: 100%;
            max-width: 650px;
            z-index: 1;
        }
        
        .register-box {
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
        
        .register-header {
            background: linear-gradient(135deg, #1a3a8f 0%, #2c5aa0 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
        }
        
        .register-header i {
            font-size: 45px;
            margin-bottom: 15px;
            color: #4CAF50;
            background: white;
            width: 85px;
            height: 85px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .register-header h2 {
            font-size: 26px;
            margin-bottom: 8px;
        }
        
        .register-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .register-body {
            padding: 35px 30px;
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
            margin-bottom: 20px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-box {
                margin: 20px;
            }
            
            .register-header {
                padding: 30px 20px;
            }
            
            .register-header h2 {
                font-size: 22px;
            }
            
            .register-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2>Employee Registration</h2>
                <p>Create your account to access the system</p>
            </div>
            
            <div class="register-body">
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
                        <label for="name">
                            <i class="fas fa-user"></i> Full Name *
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Enter your full name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter your email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="Enter phone number"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="department_id">
                                <i class="fas fa-building"></i> Department *
                            </label>
                            <select class="form-control" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                                    <option value="<?php echo $dept['department_id']; ?>"
                                        <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['department_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="designation">
                                <i class="fas fa-briefcase"></i> Designation *
                            </label>
                            <input type="text" class="form-control" id="designation" name="designation" 
                                   placeholder="e.g., Software Developer" required
                                   value="<?php echo isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="joining_date">
                            <i class="fas fa-calendar"></i> Joining Date *
                        </label>
                        <input type="date" class="form-control" id="joining_date" name="joining_date" 
                               required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo isset($_POST['joining_date']) ? $_POST['joining_date'] : date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password *
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Min. 6 characters" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i> Confirm Password *
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Re-enter password" required minlength="6">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Already have an account? <a href="employee_login.php">Login here</a></p>
                <p><a href="index.html">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
