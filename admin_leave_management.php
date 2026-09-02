<?php
require_once 'config.php';
check_admin_login();

$success = '';
$error = '';

// Handle leave approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $leave_id = (int)$_POST['leave_id'];
    $action = $_POST['action'];
    $admin_remarks = sanitize_input($_POST['admin_remarks']);
    $admin_id = $_SESSION['admin_id'];
    
    if ($action == 'approve' || $action == 'reject') {
        $status = ($action == 'approve') ? 'approved' : 'rejected';
        
        $query = "UPDATE leave_requests 
                  SET status='$status', admin_remarks='$admin_remarks', 
                      processed_by=$admin_id, processed_at=NOW() 
                  WHERE leave_id=$leave_id";
        
        if (mysqli_query($conn, $query)) {
            $success = "Leave request $status successfully!";
            
            // If approved, mark attendance as on_leave
            if ($status == 'approved') {
                $leave_query = "SELECT employee_id, start_date, end_date FROM leave_requests WHERE leave_id=$leave_id";
                $leave_result = mysqli_query($conn, $leave_query);
                $leave_data = mysqli_fetch_assoc($leave_result);
                
                // Mark each day as on_leave
                $current_date = $leave_data['start_date'];
                while (strtotime($current_date) <= strtotime($leave_data['end_date'])) {
                    $check_att = "SELECT * FROM attendance 
                                  WHERE employee_id={$leave_data['employee_id']} AND date='$current_date'";
                    $check_result = mysqli_query($conn, $check_att);
                    
                    if (mysqli_num_rows($check_result) == 0) {
                        $insert_att = "INSERT INTO attendance (employee_id, date, status, remarks, marked_by) 
                                      VALUES ({$leave_data['employee_id']}, '$current_date', 'on_leave', 
                                              'Leave approved', 'admin')";
                        mysqli_query($conn, $insert_att);
                    } else {
                        $update_att = "UPDATE attendance SET status='on_leave', remarks='Leave approved' 
                                      WHERE employee_id={$leave_data['employee_id']} AND date='$current_date'";
                        mysqli_query($conn, $update_att);
                    }
                    
                    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                }
            }
            
            log_activity('admin', $admin_id, 'Process Leave', "$status leave request ID: $leave_id");
        } else {
            $error = 'Error processing leave request';
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : 'all';
$department_filter = isset($_GET['department']) ? (int)$_GET['department'] : 0;

// Get leave requests
$query = "SELECT lr.*, e.name as employee_name, e.email, d.department_name, a.name as admin_name
          FROM leave_requests lr
          JOIN employees e ON lr.employee_id = e.employee_id
          LEFT JOIN departments d ON e.department_id = d.department_id
          LEFT JOIN admins a ON lr.processed_by = a.admin_id
          WHERE 1=1";

if ($status_filter != 'all') {
    $query .= " AND lr.status = '$status_filter'";
}

if ($department_filter > 0) {
    $query .= " AND e.department_id = $department_filter";
}

$query .= " ORDER BY 
            CASE lr.status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'rejected' THEN 3 
            END, 
            lr.applied_at DESC";

$result_leaves = mysqli_query($conn, $query);

// Get departments
$query_departments = "SELECT * FROM departments ORDER BY department_name";
$result_departments = mysqli_query($conn, $query_departments);

// Statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
                FROM leave_requests";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
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
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/admin_topbar.php'; ?>
            
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
                
                <!-- Statistics -->
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['total']; ?></h3>
                            <p>Total Requests</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['pending']; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['approved']; ?></h3>
                            <p>Approved</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $stats['rejected']; ?></h3>
                            <p>Rejected</p>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="form-container mb-20">
                    <form method="GET" class="flex gap-10 align-center">
                        <label><strong>Filters:</strong></label>
                        
                        <select name="status" class="form-control" style="width: 200px;">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                        
                        <select name="department" class="form-control" style="width: 200px;">
                            <option value="0">All Departments</option>
                            <?php while ($dept = mysqli_fetch_assoc($result_departments)): ?>
                                <option value="<?php echo $dept['department_id']; ?>" 
                                        <?php echo $department_filter == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo $dept['department_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="admin_leave_management.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </form>
                </div>
                
                <!-- Leave Requests Table -->
                <div class="table-container">
                    <h3 class="mb-20">Leave Requests</h3>
                    
                    <?php if (mysqli_num_rows($result_leaves) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = mysqli_fetch_assoc($result_leaves)): ?>
                                <?php
                                $days = (strtotime($leave['end_date']) - strtotime($leave['start_date'])) / (60 * 60 * 24) + 1;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $leave['employee_name']; ?></strong><br>
                                        <small><?php echo $leave['email']; ?></small>
                                    </td>
                                    <td><?php echo $leave['department_name'] ?: 'N/A'; ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($leave['leave_type']); ?></span></td>
                                    <td>
                                        <?php 
                                        echo date('M d', strtotime($leave['start_date'])) . ' - ' . 
                                             date('M d, Y', strtotime($leave['end_date']));
                                        ?>
                                    </td>
                                    <td><?php echo $days; ?></td>
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
                                        <?php if ($leave['admin_remarks']): ?>
                                            <br><small><?php echo $leave['admin_remarks']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($leave['applied_at'])); ?></td>
                                    <td>
                                        <?php if ($leave['status'] == 'pending'): ?>
                                            <button onclick='processLeave(<?php echo json_encode($leave); ?>)' 
                                                    class="btn btn-info btn-sm">
                                                <i class="fas fa-tasks"></i> Process
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Processed</span>
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
    
    <!-- Process Leave Modal -->
    <div id="processModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Process Leave Request</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="leaveDetails" class="mb-20"></div>
                
                <form method="POST" id="processForm">
                    <input type="hidden" name="leave_id" id="leave_id">
                    <input type="hidden" name="action" id="leave_action">
                    
                    <div class="form-group">
                        <label>Admin Remarks</label>
                        <textarea name="admin_remarks" class="form-control" rows="3" 
                                  placeholder="Add your remarks (optional)"></textarea>
                    </div>
                    
                    <div class="flex gap-10">
                        <button type="button" onclick="submitLeaveAction('approve')" class="btn btn-success">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" onclick="submitLeaveAction('reject')" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function processLeave(leave) {
            const days = Math.ceil((new Date(leave.end_date) - new Date(leave.start_date)) / (1000 * 60 * 60 * 24)) + 1;
            
            const details = `
                <div class="alert alert-info">
                    <strong>Employee:</strong> ${leave.employee_name}<br>
                    <strong>Leave Type:</strong> ${leave.leave_type}<br>
                    <strong>Duration:</strong> ${leave.start_date} to ${leave.end_date} (${days} days)<br>
                    <strong>Reason:</strong> ${leave.reason}
                </div>
            `;
            
            document.getElementById('leaveDetails').innerHTML = details;
            document.getElementById('leave_id').value = leave.leave_id;
            document.getElementById('processModal').style.display = 'block';
        }
        
        function submitLeaveAction(action) {
            if (confirm(`Are you sure you want to ${action} this leave request?`)) {
                document.getElementById('leave_action').value = action;
                document.getElementById('processForm').submit();
            }
        }
        
        function closeModal() {
            document.getElementById('processModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('processModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
