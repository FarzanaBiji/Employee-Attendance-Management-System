<?php
require_once 'config.php';
check_employee_login();

$employee_id = $_SESSION['employee_id'];
$today = date('Y-m-d');
$current_month = date('Y-m');

// Get employee details
$query_employee = "SELECT e.*, d.department_name 
                   FROM employees e 
                   LEFT JOIN departments d ON e.department_id = d.department_id 
                   WHERE e.employee_id = $employee_id";
$result_employee = mysqli_query($conn, $query_employee);
$employee = mysqli_fetch_assoc($result_employee);

// Today's attendance status
$query_today = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = '$today'";
$result_today = mysqli_query($conn, $query_today);
$today_attendance = mysqli_fetch_assoc($result_today);

// This month statistics
$query_month_stats = "SELECT 
                      COUNT(*) as total_days,
                      SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days,
                      SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                      SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as leave_days
                      FROM attendance 
                      WHERE employee_id = $employee_id AND DATE_FORMAT(date, '%Y-%m') = '$current_month'";
$result_month_stats = mysqli_query($conn, $query_month_stats);
$month_stats = mysqli_fetch_assoc($result_month_stats);

// Pending leave requests
$query_pending_leaves = "SELECT COUNT(*) as pending FROM leave_requests 
                         WHERE employee_id = $employee_id AND status = 'pending'";
$result_pending = mysqli_query($conn, $query_pending_leaves);
$pending_leaves = mysqli_fetch_assoc($result_pending)['pending'];

// Recent attendance history
$query_recent = "SELECT * FROM attendance 
                 WHERE employee_id = $employee_id 
                 ORDER BY date DESC LIMIT 10";
$result_recent = mysqli_query($conn, $query_recent);

// Recent leave requests
$query_leaves = "SELECT * FROM leave_requests 
                 WHERE employee_id = $employee_id 
                 ORDER BY applied_at DESC LIMIT 5";
$result_leaves = mysqli_query($conn, $query_leaves);

$attendance_percentage = $month_stats['total_days'] > 0 ? 
    round(($month_stats['present_days'] / $month_stats['total_days']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/employee_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/employee_topbar.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Welcome, <?php echo $employee['name']; ?>!</h1>
                    <p class="breadcrumb">Home / Dashboard</p>
                </div>
                
                <!-- Today's Attendance -->
                <div class="form-container mb-20">
                    <h3 class="mb-20">Today's Attendance</h3>
                    
                    <?php if ($today_attendance): ?>
                        <div class="alert alert-success">
                            <div class="flex justify-between align-center">
                                <div>
                                    <i class="fas fa-check-circle"></i>
                                    <strong>You have marked attendance today!</strong>
                                    <p style="margin-top: 5px;">
                                        Check-in: <?php echo date('h:i A', strtotime($today_attendance['check_in_time'])); ?>
                                        <?php if ($today_attendance['check_out_time']): ?>
                                            | Check-out: <?php echo date('h:i A', strtotime($today_attendance['check_out_time'])); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="badge badge-success" style="font-size: 16px;">
                                    <?php echo ucfirst(str_replace('_', ' ', $today_attendance['status'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            You haven't marked attendance today!
                        </div>
                        <a href="mark_attendance.php" class="btn btn-success">
                            <i class="fas fa-calendar-check"></i> Mark Attendance Now
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Statistics Cards -->
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon success">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $month_stats['present_days']; ?></h3>
                            <p>Present This Month</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon danger">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $month_stats['absent_days']; ?></h3>
                            <p>Absent This Month</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon info">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $month_stats['leave_days']; ?></h3>
                            <p>Leaves This Month</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon primary">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $attendance_percentage; ?>%</h3>
                            <p>Attendance Rate</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Attendance History -->
                <div class="table-container mb-20">
                    <div class="flex justify-between align-center mb-20">
                        <h3>Recent Attendance History</h3>
                        <a href="employee_attendance_history.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-history"></i> View All
                        </a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($att = mysqli_fetch_assoc($result_recent)): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($att['date'])); ?></td>
                                    <td><?php echo $att['check_in_time'] ? date('h:i A', strtotime($att['check_in_time'])) : '-'; ?></td>
                                    <td><?php echo $att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $badge_class = 'badge-secondary';
                                        if ($att['status'] == 'present') $badge_class = 'badge-success';
                                        elseif ($att['status'] == 'absent') $badge_class = 'badge-danger';
                                        elseif ($att['status'] == 'late') $badge_class = 'badge-warning';
                                        elseif ($att['status'] == 'on_leave') $badge_class = 'badge-info';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $att['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $att['remarks'] ?: '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Leave Requests -->
                <div class="table-container">
                    <div class="flex justify-between align-center mb-20">
                        <h3>Recent Leave Requests</h3>
                        <a href="employee_leave_management.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Apply for Leave
                        </a>
                    </div>
                    <?php if (mysqli_num_rows($result_leaves) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = mysqli_fetch_assoc($result_leaves)): ?>
                                <tr>
                                    <td><span class="badge badge-info"><?php echo ucfirst($leave['leave_type']); ?></span></td>
                                    <td>
                                        <?php 
                                        echo date('M d', strtotime($leave['start_date'])) . ' - ' . 
                                             date('M d, Y', strtotime($leave['end_date']));
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($leave['applied_at'])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'badge-warning';
                                        if ($leave['status'] == 'approved') $status_class = 'badge-success';
                                        elseif ($leave['status'] == 'rejected') $status_class = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $leave['admin_remarks'] ?: '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p class="text-center" style="color: #999; padding: 20px;">No leave requests yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
