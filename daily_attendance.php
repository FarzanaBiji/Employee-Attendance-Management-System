<?php
require_once 'config.php';
check_admin_login();

$success = '';
$error = '';
$selected_date = isset($_GET['date']) ? sanitize_input($_GET['date']) : date('Y-m-d');

// Handle manual attendance updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'update_attendance') {
        $employee_id = (int)$_POST['employee_id'];
        $date = sanitize_input($_POST['date']);
        $status = sanitize_input($_POST['status']);
        $remarks = sanitize_input($_POST['remarks']);
        $check_in = sanitize_input($_POST['check_in_time']);
        $check_out = sanitize_input($_POST['check_out_time']);
        
        // Check if attendance exists
        $check_query = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = '$date'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing
            $query = "UPDATE attendance SET status='$status', remarks='$remarks', 
                      check_in_time=" . ($check_in ? "'$check_in'" : "NULL") . ", 
                      check_out_time=" . ($check_out ? "'$check_out'" : "NULL") . ", 
                      marked_by='admin' WHERE employee_id=$employee_id AND date='$date'";
        } else {
            // Insert new
            $query = "INSERT INTO attendance (employee_id, date, status, remarks, 
                      check_in_time, check_out_time, marked_by) 
                      VALUES ($employee_id, '$date', '$status', '$remarks', 
                      " . ($check_in ? "'$check_in'" : "NULL") . ", 
                      " . ($check_out ? "'$check_out'" : "NULL") . ", 'admin')";
        }
        
        if (mysqli_query($conn, $query)) {
            $success = 'Attendance updated successfully!';
            log_activity('admin', $_SESSION['admin_id'], 'Update Attendance', 
                        "Updated attendance for employee ID: $employee_id on $date");
        } else {
            $error = 'Error updating attendance: ' . mysqli_error($conn);
        }
    }
}

// Get all employees with their attendance for selected date
$query = "SELECT e.employee_id, e.name, e.email, d.department_name, e.shift_start, e.shift_end,
          a.attendance_id, a.check_in_time, a.check_out_time, a.status, a.remarks, a.marked_by
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.department_id
          LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = '$selected_date'
          WHERE e.status = 'active'
          ORDER BY e.name";
$result = mysqli_query($conn, $query);

// Statistics for selected date
$total_employees = 0;
$present_count = 0;
$absent_count = 0;
$late_count = 0;
$on_leave_count = 0;

$temp_result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($temp_result)) {
    $total_employees++;
    $status = $row['status'] ?: 'absent';
    if ($status == 'present') $present_count++;
    elseif ($status == 'late') $late_count++;
    elseif ($status == 'on_leave') $on_leave_count++;
    else $absent_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="page-title">Daily Attendance</h1>
                    <p class="breadcrumb">Home / Daily Attendance</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Date Selector -->
                <div class="form-container mb-20">
                    <form method="GET" class="flex gap-10 align-center">
                        <label><strong>Select Date:</strong></label>
                        <input type="date" name="date" class="form-control" 
                               value="<?php echo $selected_date; ?>" style="width: 200px;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-calendar"></i> Load Attendance
                        </button>
                        <a href="daily_attendance.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-calendar-day"></i> Today
                        </a>
                    </form>
                </div>
                
                <!-- Statistics -->
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
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $present_count; ?></h3>
                            <p>Present</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $late_count; ?></h3>
                            <p>Late</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $absent_count; ?></h3>
                            <p>Absent</p>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Table -->
                <div class="table-container">
                    <h3 class="mb-20">Attendance for <?php echo date('F j, Y', strtotime($selected_date)); ?></h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Shift Time</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Marked By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($emp = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $emp['name']; ?></td>
                                    <td><?php echo $emp['department_name'] ?: 'N/A'; ?></td>
                                    <td>
                                        <?php echo date('h:i A', strtotime($emp['shift_start'])) . ' - ' . 
                                                   date('h:i A', strtotime($emp['shift_end'])); ?>
                                    </td>
                                    <td><?php echo $emp['check_in_time'] ? date('h:i A', strtotime($emp['check_in_time'])) : '-'; ?></td>
                                    <td><?php echo $emp['check_out_time'] ? date('h:i A', strtotime($emp['check_out_time'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status = $emp['status'] ?: 'absent';
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
                                    <td>
                                        <?php if ($emp['marked_by']): ?>
                                            <span class="badge badge-secondary"><?php echo ucfirst($emp['marked_by']); ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick='openEditModal(<?php echo json_encode($emp); ?>, "<?php echo $selected_date; ?>")' 
                                                class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Attendance Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Attendance</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_attendance">
                    <input type="hidden" name="employee_id" id="employee_id">
                    <input type="hidden" name="date" id="attendance_date">
                    
                    <div class="form-group">
                        <label>Employee Name</label>
                        <input type="text" id="employee_name" class="form-control" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check-in Time</label>
                            <input type="time" name="check_in_time" id="check_in_time" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Check-out Time</label>
                            <input type="time" name="check_out_time" id="check_out_time" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Attendance</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openEditModal(employee, date) {
            document.getElementById('employee_id').value = employee.employee_id;
            document.getElementById('employee_name').value = employee.name;
            document.getElementById('attendance_date').value = date;
            document.getElementById('check_in_time').value = employee.check_in_time || '';
            document.getElementById('check_out_time').value = employee.check_out_time || '';
            document.getElementById('status').value = employee.status || 'absent';
            document.getElementById('remarks').value = employee.remarks || '';
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
