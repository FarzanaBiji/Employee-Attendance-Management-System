<?php
require_once 'config.php';
check_employee_login();

$employee_id = $_SESSION['employee_id'];
$today = date('Y-m-d');
$current_time = date('H:i:s');

$success = '';
$error = '';

// Get employee shift details
$query_emp = "SELECT * FROM employees WHERE employee_id = $employee_id";
$result_emp = mysqli_query($conn, $query_emp);
$employee = mysqli_fetch_assoc($result_emp);

// Check if already marked attendance
$query_check = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = '$today'";
$result_check = mysqli_query($conn, $query_check);
$existing_attendance = mysqli_fetch_assoc($result_check);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'check_in') {
        if ($existing_attendance) {
            $error = 'You have already checked in today!';
        } else {
            // Determine status based on time
            $status = calculate_late_status($current_time, $employee['shift_start']);
            
            $query = "INSERT INTO attendance (employee_id, date, check_in_time, status, marked_by) 
                      VALUES ($employee_id, '$today', '$current_time', '$status', 'self')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Attendance marked successfully!';
                log_activity('employee', $employee_id, 'Mark Attendance', "Checked in at $current_time");
                // Refresh to get updated data
                header("Refresh:0");
            } else {
                $error = 'Error marking attendance: ' . mysqli_error($conn);
            }
        }
    }
    
    elseif ($action == 'check_out') {
        if (!$existing_attendance) {
            $error = 'You need to check in first!';
        } elseif ($existing_attendance['check_out_time']) {
            $error = 'You have already checked out today!';
        } else {
            $query = "UPDATE attendance SET check_out_time = '$current_time' 
                      WHERE employee_id = $employee_id AND date = '$today'";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Check-out recorded successfully!';
                log_activity('employee', $employee_id, 'Check Out', "Checked out at $current_time");
                // Refresh to get updated data
                header("Refresh:0");
            } else {
                $error = 'Error recording check-out: ' . mysqli_error($conn);
            }
        }
    }
}

// Recalculate existing attendance after potential POST
$result_check = mysqli_query($conn, $query_check);
$existing_attendance = mysqli_fetch_assoc($result_check);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .attendance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        .time-display {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
        }
        .date-display {
            font-size: 20px;
            opacity: 0.9;
        }
        .attendance-button {
            margin: 10px;
            padding: 20px 40px;
            font-size: 18px;
            min-width: 200px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/employee_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/employee_topbar.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Mark Attendance</h1>
                    <p class="breadcrumb">Home / Mark Attendance</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Attendance Card -->
                <div class="attendance-card">
                    <div class="date-display"><?php echo date('l, F j, Y'); ?></div>
                    <div class="time-display" id="currentTime"><?php echo date('h:i:s A'); ?></div>
                    <p>Your shift: <?php echo date('h:i A', strtotime($employee['shift_start'])); ?> - 
                       <?php echo date('h:i A', strtotime($employee['shift_end'])); ?></p>
                </div>
                
                <!-- Attendance Actions -->
                <div class="form-container text-center">
                    <?php if (!$existing_attendance): ?>
                        <!-- Not checked in yet -->
                        <h3 class="mb-20">You haven't marked attendance today</h3>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="action" value="check_in">
                            <button type="submit" class="btn btn-success attendance-button">
                                <i class="fas fa-sign-in-alt"></i> Check In
                            </button>
                        </form>
                    <?php elseif (!$existing_attendance['check_out_time']): ?>
                        <!-- Checked in, not checked out -->
                        <div class="alert alert-success">
                            <h3>✓ You're checked in!</h3>
                            <p>Check-in time: <?php echo date('h:i A', strtotime($existing_attendance['check_in_time'])); ?></p>
                            <p>Status: <strong><?php echo ucfirst(str_replace('_', ' ', $existing_attendance['status'])); ?></strong></p>
                        </div>
                        
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="action" value="check_out">
                            <button type="submit" class="btn btn-danger attendance-button">
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Already checked out -->
                        <div class="alert alert-info">
                            <h3>✓ Attendance Completed for Today!</h3>
                            <p>Check-in: <?php echo date('h:i A', strtotime($existing_attendance['check_in_time'])); ?></p>
                            <p>Check-out: <?php echo date('h:i A', strtotime($existing_attendance['check_out_time'])); ?></p>
                            <p>Status: <strong><?php echo ucfirst(str_replace('_', ' ', $existing_attendance['status'])); ?></strong></p>
                            
                            <?php
                            $worked_hours = (strtotime($existing_attendance['check_out_time']) - 
                                           strtotime($existing_attendance['check_in_time'])) / 3600;
                            ?>
                            <p>Total hours worked: <strong><?php echo number_format($worked_hours, 2); ?> hours</strong></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Info Section -->
                <div class="form-container mt-20">
                    <h3 class="mb-20">Attendance Guidelines</h3>
                    <ul style="line-height: 2; padding-left: 20px;">
                        <li><i class="fas fa-check-circle" style="color: var(--success-color);"></i> 
                            Check in before <?php echo date('h:i A', strtotime($employee['shift_start'])); ?> to mark on-time attendance</li>
                        <li><i class="fas fa-clock" style="color: var(--warning-color);"></i> 
                            15 minutes grace period after shift start</li>
                        <li><i class="fas fa-exclamation-triangle" style="color: var(--danger-color);"></i> 
                            Check-in after grace period will be marked as "Late"</li>
                        <li><i class="fas fa-sign-out-alt" style="color: var(--info-color);"></i> 
                            Remember to check out at the end of your shift</li>
                        <li><i class="fas fa-umbrella-beach" style="color: var(--primary-color);"></i> 
                            For leaves, apply through Leave Management section</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Update time every second
        setInterval(function() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            
            const timeString = String(displayHours).padStart(2, '0') + ':' + 
                             String(minutes).padStart(2, '0') + ':' + 
                             String(seconds).padStart(2, '0') + ' ' + ampm;
            
            document.getElementById('currentTime').textContent = timeString;
        }, 1000);
    </script>
</body>
</html>
