<?php
require_once 'config.php';
check_admin_login();

$report_data = [];
$show_report = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_type = $_POST['report_type'];
    $show_report = true;
    
    if ($report_type == 'date_range') {
        $start_date = sanitize_input($_POST['start_date']);
        $end_date = sanitize_input($_POST['end_date']);
        $employee_id = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
        
        $query = "SELECT e.employee_id, e.name, d.department_name,
                  COUNT(a.attendance_id) as total_days,
                  SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days,
                  SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                  SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                  SUM(CASE WHEN a.status = 'on_leave' THEN 1 ELSE 0 END) as leave_days
                  FROM employees e
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  LEFT JOIN attendance a ON e.employee_id = a.employee_id 
                      AND a.date BETWEEN '$start_date' AND '$end_date'
                  WHERE e.status = 'active'";
        
        if ($employee_id > 0) {
            $query .= " AND e.employee_id = $employee_id";
        }
        
        $query .= " GROUP BY e.employee_id ORDER BY e.name";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        
        $report_title = "Attendance Report: " . date('M d, Y', strtotime($start_date)) . 
                       " to " . date('M d, Y', strtotime($end_date));
    }
    
    elseif ($report_type == 'monthly') {
        $month = sanitize_input($_POST['month']);
        $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
        
        $query = "SELECT e.employee_id, e.name, d.department_name,
                  COUNT(a.attendance_id) as total_days,
                  SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) as present_days,
                  SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                  SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                  SUM(CASE WHEN a.status = 'on_leave' THEN 1 ELSE 0 END) as leave_days
                  FROM employees e
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  LEFT JOIN attendance a ON e.employee_id = a.employee_id 
                      AND DATE_FORMAT(a.date, '%Y-%m') = '$month'
                  WHERE e.status = 'active'";
        
        if ($department_id > 0) {
            $query .= " AND e.department_id = $department_id";
        }
        
        $query .= " GROUP BY e.employee_id ORDER BY e.name";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
        
        $report_title = "Monthly Attendance Report: " . date('F Y', strtotime($month . '-01'));
    }
}

// Get all employees for filter
$query_employees = "SELECT employee_id, name FROM employees WHERE status = 'active' ORDER BY name";
$result_employees = mysqli_query($conn, $query_employees);

// Get all departments for filter
$query_departments = "SELECT * FROM departments ORDER BY department_name";
$result_departments = mysqli_query($conn, $query_departments);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="page-title">Attendance Reports</h1>
                    <p class="breadcrumb">Home / Reports</p>
                </div>
                
                <!-- Report Filters -->
                <div class="form-container mb-20">
                    <h3 class="mb-20">Generate Report</h3>
                    
                    <div class="form-group">
                        <label>Report Type</label>
                        <select id="reportType" class="form-control" onchange="toggleReportForms()">
                            <option value="">Select Report Type</option>
                            <option value="date_range">Date Range Report</option>
                            <option value="monthly">Monthly Report</option>
                        </select>
                    </div>
                    
                    <!-- Date Range Form -->
                    <form method="POST" id="dateRangeForm" style="display: none;">
                        <input type="hidden" name="report_type" value="date_range">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>End Date *</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Employee (Optional)</label>
                                <select name="employee_id" class="form-control">
                                    <option value="0">All Employees</option>
                                    <?php while ($emp = mysqli_fetch_assoc($result_employees)): ?>
                                        <option value="<?php echo $emp['employee_id']; ?>">
                                            <?php echo $emp['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Generate Report
                        </button>
                    </form>
                    
                    <!-- Monthly Form -->
                    <form method="POST" id="monthlyForm" style="display: none;">
                        <input type="hidden" name="report_type" value="monthly">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Select Month *</label>
                                <input type="month" name="month" class="form-control" 
                                       value="<?php echo date('Y-m'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Department (Optional)</label>
                                <select name="department_id" class="form-control">
                                    <option value="0">All Departments</option>
                                    <?php while ($dept = mysqli_fetch_assoc($result_departments)): ?>
                                        <option value="<?php echo $dept['department_id']; ?>">
                                            <?php echo $dept['department_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Generate Report
                        </button>
                    </form>
                </div>
                
                <!-- Report Results -->
                <?php if ($show_report && count($report_data) > 0): ?>
                <div class="table-container" id="reportTable">
                    <div class="flex justify-between align-center mb-20">
                        <h3><?php echo $report_title; ?></h3>
                        <button onclick="printReport()" class="btn btn-info btn-sm">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Total Days</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Leave</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row): ?>
                                <?php 
                                $attendance_percentage = $row['total_days'] > 0 ? 
                                    round(($row['present_days'] / $row['total_days']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['department_name'] ?: 'N/A'; ?></td>
                                    <td><?php echo $row['total_days']; ?></td>
                                    <td><span class="badge badge-success"><?php echo $row['present_days']; ?></span></td>
                                    <td><span class="badge badge-danger"><?php echo $row['absent_days']; ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo $row['late_days']; ?></span></td>
                                    <td><span class="badge badge-info"><?php echo $row['leave_days']; ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $attendance_percentage >= 80 ? 'badge-success' : 
                                            ($attendance_percentage >= 60 ? 'badge-warning' : 'badge-danger'); ?>">
                                            <?php echo $attendance_percentage; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($show_report): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No attendance records found for the selected criteria.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleReportForms() {
            const reportType = document.getElementById('reportType').value;
            document.getElementById('dateRangeForm').style.display = 'none';
            document.getElementById('monthlyForm').style.display = 'none';
            
            if (reportType == 'date_range') {
                document.getElementById('dateRangeForm').style.display = 'block';
            } else if (reportType == 'monthly') {
                document.getElementById('monthlyForm').style.display = 'block';
            }
        }
        
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
