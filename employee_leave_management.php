<?php
require_once 'config.php';
check_employee_login();

$employee_id = $_SESSION['employee_id'];
$success = '';
$error = '';

// Handle leave application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'apply_leave') {
        $leave_type = sanitize_input($_POST['leave_type']);
        $start_date = sanitize_input($_POST['start_date']);
        $end_date = sanitize_input($_POST['end_date']);
        $reason = sanitize_input($_POST['reason']);
        
        // Validate dates
        if (strtotime($end_date) < strtotime($start_date)) {
            $error = 'End date cannot be before start date!';
        } else {
            $query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) 
                      VALUES ($employee_id, '$leave_type', '$start_date', '$end_date', '$reason')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Leave request submitted successfully!';
                log_activity('employee', $employee_id, 'Apply Leave', "Applied for $leave_type leave");
            } else {
                $error = 'Error submitting leave request: ' . mysqli_error($conn);
            }
        }
    }
    
    elseif ($_POST['action'] == 'cancel_leave') {
        $leave_id = (int)$_POST['leave_id'];
        
        // Can only cancel pending leaves
        $query = "DELETE FROM leave_requests 
                  WHERE leave_id = $leave_id AND employee_id = $employee_id AND status = 'pending'";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Leave request cancelled successfully!';
            log_activity('employee', $employee_id, 'Cancel Leave', "Cancelled leave request ID: $leave_id");
        } else {
            $error = 'Error cancelling leave request';
        }
    }
}

// Get all leave requests
$query = "SELECT * FROM leave_requests 
          WHERE employee_id = $employee_id 
          ORDER BY applied_at DESC";
$result_leaves = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="page-title">Leave Management</h1>
                    <p class="breadcrumb">Home / Leave Management</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Apply for Leave -->
                <div class="form-container mb-20">
                    <h3 class="mb-20">Apply for Leave</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="apply_leave">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Leave Type *</label>
                                <select name="leave_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="casual">Casual Leave</option>
                                    <option value="annual">Annual Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" name="start_date" class="form-control" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>End Date *</label>
                                <input type="date" name="end_date" class="form-control" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Reason *</label>
                            <textarea name="reason" class="form-control" rows="3" required 
                                      placeholder="Please provide a reason for your leave request"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Submit Leave Request
                        </button>
                    </form>
                </div>
                
                <!-- Leave History -->
                <div class="table-container">
                    <h3 class="mb-20">My Leave Requests</h3>
                    
                    <?php if (mysqli_num_rows($result_leaves) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Admin Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = mysqli_fetch_assoc($result_leaves)): ?>
                                <?php
                                $days = (strtotime($leave['end_date']) - strtotime($leave['start_date'])) / (60 * 60 * 24) + 1;
                                ?>
                                <tr>
                                    <td><span class="badge badge-info"><?php echo ucfirst($leave['leave_type']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($leave['end_date'])); ?></td>
                                    <td><?php echo $days; ?> day(s)</td>
                                    <td><?php echo $leave['reason']; ?></td>
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
                                    <td><?php echo date('M d, Y', strtotime($leave['applied_at'])); ?></td>
                                    <td><?php echo $leave['admin_remarks'] ?: '-'; ?></td>
                                    <td>
                                        <?php if ($leave['status'] == 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="cancel_leave">
                                                <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Cancel this leave request?')">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p class="text-center" style="color: #999; padding: 20px;">No leave requests found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
