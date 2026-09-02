<?php
require_once 'config.php';
check_employee_login();

$employee_id = $_SESSION['employee_id'];

// Get filter parameters
$month = isset($_GET['month']) ? sanitize_input($_GET['month']) : date('Y-m');

// Get attendance history
$query = "SELECT * FROM attendance 
          WHERE employee_id = $employee_id AND DATE_FORMAT(date, '%Y-%m') = '$month'
          ORDER BY date DESC";
$result_attendance = mysqli_query($conn, $query);

// Calculate statistics
$stats_query = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as leave_days
                FROM attendance 
                WHERE employee_id = $employee_id AND DATE_FORMAT(date, '%Y-%m') = '$month'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$attendance_percentage = $stats['total_days'] > 0 ? 
    round(($stats['present_days'] / $stats['total_days']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="page-title">Attendance History</h1>
                    <p class="breadcrumb">Home / Attendance History</p>
                </div>
                
                <!-- Month Selector -->
                <div class="form-container mb-20">
                    <form method="GET" class="flex gap-10 align-center">
                        <label><strong>Select Month:</strong></label>
                        <input type="month" name="month" class="form-control" 
                               value="<?php echo $month; ?>" style="width: 200px;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-calendar"></i> Load History
                        </button>
                    </form>
                </div>
                
                <!-- Statistics -->
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['present_days']; ?></h3>
                            <p>Present Days</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['absent_days']; ?></h3>
                            <p>Absent Days</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['late_days']; ?></h3>
                            <p>Late Days</p>
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
                
                <!-- Attendance History Table -->
                <div class="table-container">
                    <h3 class="mb-20">Attendance for <?php echo date('F Y', strtotime($month . '-01')); ?></h3>
                    
                    <?php if (mysqli_num_rows($result_attendance) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Check-in Time</th>
                                <th>Check-out Time</th>
                                <th>Working Hours</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($att = mysqli_fetch_assoc($result_attendance)): ?>
                                <?php
                                $working_hours = 0;
                                if ($att['check_in_time'] && $att['check_out_time']) {
                                    $working_hours = (strtotime($att['check_out_time']) - 
                                                    strtotime($att['check_in_time'])) / 3600;
                                }
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($att['date'])); ?></td>
                                    <td><?php echo date('l', strtotime($att['date'])); ?></td>
                                    <td><?php echo $att['check_in_time'] ? date('h:i A', strtotime($att['check_in_time'])) : '-'; ?></td>
                                    <td><?php echo $att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-'; ?></td>
                                    <td><?php echo $working_hours > 0 ? number_format($working_hours, 2) . ' hrs' : '-'; ?></td>
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
                    <?php else: ?>
                        <p class="text-center" style="color: #999; padding: 20px;">
                            No attendance records found for <?php echo date('F Y', strtotime($month . '-01')); ?>.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
