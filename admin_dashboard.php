<?php
require_once 'config.php';
check_admin_login();

// Get dashboard statistics
$today = date('Y-m-d');

// Total employees
$query_total = "SELECT COUNT(*) as total FROM employees WHERE status = 'active'";
$result_total = mysqli_query($conn, $query_total);
$total_employees = mysqli_fetch_assoc($result_total)['total'];

// Today present count
$query_present = "SELECT COUNT(*) as present FROM attendance 
                  WHERE date = '$today' AND status IN ('present', 'late')";
$result_present = mysqli_query($conn, $query_present);
$present_today = mysqli_fetch_assoc($result_present)['present'];

// Today absent count
$absent_today = $total_employees - $present_today;

// Pending leave requests
$query_leaves = "SELECT COUNT(*) as pending FROM leave_requests WHERE status = 'pending'";
$result_leaves = mysqli_query($conn, $query_leaves);
$pending_leaves = mysqli_fetch_assoc($result_leaves)['pending'];

// Recent leave requests
$query_recent = "SELECT lr.*, e.name as employee_name, e.department_id, d.department_name 
                 FROM leave_requests lr 
                 JOIN employees e ON lr.employee_id = e.employee_id 
                 LEFT JOIN departments d ON e.department_id = d.department_id 
                 WHERE lr.status = 'pending' 
                 ORDER BY lr.applied_at DESC LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);

// Today's attendance summary
$query_today_attendance = "SELECT e.employee_id, e.name, e.email, d.department_name, 
                           a.check_in_time, a.status, e.shift_start
                           FROM employees e 
                           LEFT JOIN departments d ON e.department_id = d.department_id
                           LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = '$today'
                           WHERE e.status = 'active'
                           ORDER BY a.check_in_time DESC LIMIT 10";
$result_today_attendance = mysqli_query($conn, $query_today_attendance);

// Department-wise attendance
$query_dept = "SELECT d.department_name, 
               COUNT(DISTINCT e.employee_id) as total,
               COUNT(DISTINCT CASE WHEN a.status IN ('present', 'late') THEN a.employee_id END) as present
               FROM departments d
               LEFT JOIN employees e ON d.department_id = e.department_id AND e.status = 'active'
               LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = '$today'
               GROUP BY d.department_id, d.department_name";
$result_dept = mysqli_query($conn, $query_dept);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <?php include 'includes/admin_topbar.php'; ?>
            
            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="breadcrumb">Home / Dashboard</p>
                </div>
                
                <!-- Dashboard Cards -->
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $total_employees; ?></h3>
                            <p>Total Employees</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon success">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $present_today; ?></h3>
                            <p>Present Today</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon danger">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $absent_today; ?></h3>
                            <p>Absent Today</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon warning">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $pending_leaves; ?></h3>
                            <p>Pending Leave Requests</p>
                        </div>
                    </div>
                </div>
                
                <!-- Department-wise Attendance -->
                <div class="table-container mb-20">
                    <div class="flex justify-between align-center mb-20">
                        <h3>Department-wise Attendance (Today)</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Total Employees</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dept = mysqli_fetch_assoc($result_dept)): ?>
                                <?php 
                                    $absent = $dept['total'] - $dept['present'];
                                    $percentage = $dept['total'] > 0 ? round(($dept['present'] / $dept['total']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo $dept['department_name']; ?></td>
                                    <td><?php echo $dept['total']; ?></td>
                                    <td><span class="badge badge-success"><?php echo $dept['present']; ?></span></td>
                                    <td><span class="badge badge-danger"><?php echo $absent; ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $percentage >= 80 ? 'badge-success' : ($percentage >= 50 ? 'badge-warning' : 'badge-danger'); ?>">
                                            <?php echo $percentage; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Recent Attendance -->
                <div class="table-container mb-20">
                    <div class="flex justify-between align-center mb-20">
                        <h3>Today's Attendance</h3>
                        <a href="daily_attendance.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Check-in Time</th>
                                <th>Shift Start</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attendance = mysqli_fetch_assoc($result_today_attendance)): ?>
                                <tr>
                                    <td><?php echo $attendance['name']; ?></td>
                                    <td><?php echo $attendance['department_name'] ?: 'N/A'; ?></td>
                                    <td>
                                        <?php echo $attendance['check_in_time'] ? date('h:i A', strtotime($attendance['check_in_time'])) : '-'; ?>
                                    </td>
                                    <td><?php echo date('h:i A', strtotime($attendance['shift_start'])); ?></td>
                                    <td>
                                        <?php
                                        $status = $attendance['status'] ?: 'absent';
                                        $badge_class = 'badge-secondary';
                                        if ($status == 'present') $badge_class = 'badge-success';
                                        elseif ($status == 'absent') $badge_class = 'badge-danger';
                                        elseif ($status == 'late') $badge_class = 'badge-warning';
                                        elseif ($status == 'on_leave') $badge_class = 'badge-info';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pending Leave Requests -->
                <?php if ($pending_leaves > 0): ?>
                <div class="table-container">
                    <div class="flex justify-between align-center mb-20">
                        <h3>Pending Leave Requests</h3>
                        <a href="admin_leave_management.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-tasks"></i> Manage All
                        </a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Applied Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = mysqli_fetch_assoc($result_recent)): ?>
                                <tr>
                                    <td><?php echo $leave['employee_name']; ?></td>
                                    <td><?php echo $leave['department_name'] ?: 'N/A'; ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($leave['leave_type']); ?></span></td>
                                    <td>
                                        <?php 
                                        echo date('M d', strtotime($leave['start_date'])) . ' - ' . 
                                             date('M d, Y', strtotime($leave['end_date']));
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($leave['applied_at'])); ?></td>
                                    <td>
                                        <a href="admin_leave_management.php?id=<?php echo $leave['leave_id']; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
