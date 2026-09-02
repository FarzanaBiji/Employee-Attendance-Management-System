<?php
require_once 'config.php';
check_admin_login();

$success = '';
$error = '';

// Handle Add/Edit/Delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $department_id = (int)$_POST['department_id'];
            $designation = sanitize_input($_POST['designation']);
            $shift_start = sanitize_input($_POST['shift_start']);
            $shift_end = sanitize_input($_POST['shift_end']);
            $joining_date = sanitize_input($_POST['joining_date']);
            $password = password_hash('employee123', PASSWORD_DEFAULT); // Default password
            
            $query = "INSERT INTO employees (name, email, password, phone, department_id, designation, 
                      shift_start, shift_end, joining_date) 
                      VALUES ('$name', '$email', '$password', '$phone', $department_id, '$designation', 
                      '$shift_start', '$shift_end', '$joining_date')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Employee added successfully! Default password: employee123';
                log_activity('admin', $_SESSION['admin_id'], 'Add Employee', "Added employee: $name");
            } else {
                $error = 'Error adding employee: ' . mysqli_error($conn);
            }
        }
        
        elseif ($action == 'edit') {
            $employee_id = (int)$_POST['employee_id'];
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $department_id = (int)$_POST['department_id'];
            $designation = sanitize_input($_POST['designation']);
            $shift_start = sanitize_input($_POST['shift_start']);
            $shift_end = sanitize_input($_POST['shift_end']);
            $joining_date = sanitize_input($_POST['joining_date']);
            $status = sanitize_input($_POST['status']);
            
            $query = "UPDATE employees SET name='$name', email='$email', phone='$phone', 
                      department_id=$department_id, designation='$designation', 
                      shift_start='$shift_start', shift_end='$shift_end', 
                      joining_date='$joining_date', status='$status' 
                      WHERE employee_id=$employee_id";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Employee updated successfully!';
                log_activity('admin', $_SESSION['admin_id'], 'Update Employee', "Updated employee ID: $employee_id");
            } else {
                $error = 'Error updating employee: ' . mysqli_error($conn);
            }
        }
        
        elseif ($action == 'delete') {
            $employee_id = (int)$_POST['employee_id'];
            
            $query = "DELETE FROM employees WHERE employee_id=$employee_id";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Employee deleted successfully!';
                log_activity('admin', $_SESSION['admin_id'], 'Delete Employee', "Deleted employee ID: $employee_id");
            } else {
                $error = 'Error deleting employee: ' . mysqli_error($conn);
            }
        }
        
        elseif ($action == 'reset_password') {
            $employee_id = (int)$_POST['employee_id'];
            $new_password = password_hash('employee123', PASSWORD_DEFAULT);
            
            $query = "UPDATE employees SET password='$new_password' WHERE employee_id=$employee_id";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Password reset successfully! New password: employee123';
                log_activity('admin', $_SESSION['admin_id'], 'Reset Password', "Reset password for employee ID: $employee_id");
            } else {
                $error = 'Error resetting password';
            }
        }
    }
}

// Get all employees
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$department_filter = isset($_GET['department']) ? (int)$_GET['department'] : 0;

$query = "SELECT e.*, d.department_name 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.department_id 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (e.name LIKE '%$search%' OR e.email LIKE '%$search%' OR e.phone LIKE '%$search%')";
}

if ($department_filter > 0) {
    $query .= " AND e.department_id = $department_filter";
}

$query .= " ORDER BY e.created_at DESC";
$result_employees = mysqli_query($conn, $query);

// Get all departments
$query_departments = "SELECT * FROM departments ORDER BY department_name";
$result_departments = mysqli_query($conn, $query_departments);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="page-title">Employee Management</h1>
                    <p class="breadcrumb">Home / Employees</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Filters and Add Button -->
                <div class="table-container mb-20">
                    <div class="flex justify-between align-center mb-20">
                        <form method="GET" class="flex gap-10">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name, email, phone..." 
                                   value="<?php echo $search; ?>" style="width: 300px;">
                            
                            <select name="department" class="form-control" style="width: 200px;">
                                <option value="0">All Departments</option>
                                <?php 
                                mysqli_data_seek($result_departments, 0);
                                while ($dept = mysqli_fetch_assoc($result_departments)): 
                                ?>
                                    <option value="<?php echo $dept['department_id']; ?>" 
                                            <?php echo $department_filter == $dept['department_id'] ? 'selected' : ''; ?>>
                                        <?php echo $dept['department_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="employee_management.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </form>
                        
                        <button onclick="openAddModal()" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add Employee
                        </button>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Shift</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($employee = mysqli_fetch_assoc($result_employees)): ?>
                                <tr>
                                    <td><?php echo $employee['employee_id']; ?></td>
                                    <td><?php echo $employee['name']; ?></td>
                                    <td><?php echo $employee['email']; ?></td>
                                    <td><?php echo $employee['phone']; ?></td>
                                    <td><?php echo $employee['department_name'] ?: 'N/A'; ?></td>
                                    <td><?php echo $employee['designation']; ?></td>
                                    <td>
                                        <?php echo date('h:i A', strtotime($employee['shift_start'])) . ' - ' . 
                                                   date('h:i A', strtotime($employee['shift_end'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $employee['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($employee['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick='openEditModal(<?php echo json_encode($employee); ?>)' 
                                                class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="resetPassword(<?php echo $employee['employee_id']; ?>)" 
                                                class="btn btn-warning btn-sm">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button onclick="deleteEmployee(<?php echo $employee['employee_id']; ?>, '<?php echo $employee['name']; ?>')" 
                                                class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
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
    
    <!-- Add Employee Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Employee</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Department *</label>
                            <select name="department_id" class="form-control" required>
                                <option value="">Select Department</option>
                                <?php 
                                mysqli_data_seek($result_departments, 0);
                                while ($dept = mysqli_fetch_assoc($result_departments)): 
                                ?>
                                    <option value="<?php echo $dept['department_id']; ?>">
                                        <?php echo $dept['department_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Designation *</label>
                            <input type="text" name="designation" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Joining Date *</label>
                            <input type="date" name="joining_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Shift Start *</label>
                            <input type="time" name="shift_start" class="form-control" value="09:00" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Shift End *</label>
                            <input type="time" name="shift_end" class="form-control" value="18:00" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Default password will be: <strong>employee123</strong>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Employee Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Employee</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST" action="" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="employee_id" id="edit_employee_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Department *</label>
                            <select name="department_id" id="edit_department_id" class="form-control" required>
                                <?php 
                                mysqli_data_seek($result_departments, 0);
                                while ($dept = mysqli_fetch_assoc($result_departments)): 
                                ?>
                                    <option value="<?php echo $dept['department_id']; ?>">
                                        <?php echo $dept['department_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Designation *</label>
                            <input type="text" name="designation" id="edit_designation" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Joining Date *</label>
                            <input type="date" name="joining_date" id="edit_joining_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Shift Start *</label>
                            <input type="time" name="shift_start" id="edit_shift_start" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Shift End *</label>
                            <input type="time" name="shift_end" id="edit_shift_end" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function openEditModal(employee) {
            document.getElementById('edit_employee_id').value = employee.employee_id;
            document.getElementById('edit_name').value = employee.name;
            document.getElementById('edit_email').value = employee.email;
            document.getElementById('edit_phone').value = employee.phone;
            document.getElementById('edit_department_id').value = employee.department_id;
            document.getElementById('edit_designation').value = employee.designation;
            document.getElementById('edit_shift_start').value = employee.shift_start;
            document.getElementById('edit_shift_end').value = employee.shift_end;
            document.getElementById('edit_joining_date').value = employee.joining_date;
            document.getElementById('edit_status').value = employee.status;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function resetPassword(employeeId) {
            if (confirm('Are you sure you want to reset password to "employee123"?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="reset_password">' +
                               '<input type="hidden" name="employee_id" value="' + employeeId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteEmployee(employeeId, employeeName) {
            if (confirm('Are you sure you want to delete ' + employeeName + '? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete">' +
                               '<input type="hidden" name="employee_id" value="' + employeeId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
