<?php
require_once 'config.php';
check_admin_login();

$success = '';
$error = '';

// Handle add/edit/delete department
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $department_name = sanitize_input($_POST['department_name']);
        $description = sanitize_input($_POST['description']);
        
        $query = "INSERT INTO departments (department_name, description) 
                  VALUES ('$department_name', '$description')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Department added successfully!';
            log_activity('admin', $_SESSION['admin_id'], 'Add Department', "Added department: $department_name");
        } else {
            $error = 'Error adding department';
        }
    }
    
    elseif ($action == 'edit') {
        $department_id = (int)$_POST['department_id'];
        $department_name = sanitize_input($_POST['department_name']);
        $description = sanitize_input($_POST['description']);
        
        $query = "UPDATE departments SET department_name='$department_name', description='$description' 
                  WHERE department_id=$department_id";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Department updated successfully!';
            log_activity('admin', $_SESSION['admin_id'], 'Update Department', "Updated department ID: $department_id");
        } else {
            $error = 'Error updating department';
        }
    }
    
    elseif ($action == 'delete') {
        $department_id = (int)$_POST['department_id'];
        
        $query = "DELETE FROM departments WHERE department_id=$department_id";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Department deleted successfully!';
            log_activity('admin', $_SESSION['admin_id'], 'Delete Department', "Deleted department ID: $department_id");
        } else {
            $error = 'Error deleting department';
        }
    }
}

// Get all departments
$query = "SELECT d.*, COUNT(e.employee_id) as employee_count 
          FROM departments d 
          LEFT JOIN employees e ON d.department_id = e.department_id AND e.status = 'active'
          GROUP BY d.department_id 
          ORDER BY d.department_name";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="page-title">Department Management</h1>
                    <p class="breadcrumb">Home / Departments</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="table-container">
                    <div class="flex justify-between align-center mb-20">
                        <h3>All Departments</h3>
                        <button onclick="openAddModal()" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add Department
                        </button>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th>Description</th>
                                <th>Employee Count</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dept = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?php echo $dept['department_name']; ?></strong></td>
                                    <td><?php echo $dept['description'] ?: '-'; ?></td>
                                    <td><span class="badge badge-primary"><?php echo $dept['employee_count']; ?> employees</span></td>
                                    <td><?php echo date('M d, Y', strtotime($dept['created_at'])); ?></td>
                                    <td>
                                        <button onclick='openEditModal(<?php echo json_encode($dept); ?>)' 
                                                class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteDepartment(<?php echo $dept['department_id']; ?>, '<?php echo $dept['department_name']; ?>')" 
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
    
    <!-- Add Department Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Department</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Department Name *</label>
                        <input type="text" name="department_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Department</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Department Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Department</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="department_id" id="edit_department_id">
                    
                    <div class="form-group">
                        <label>Department Name *</label>
                        <input type="text" name="department_name" id="edit_department_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function openEditModal(dept) {
            document.getElementById('edit_department_id').value = dept.department_id;
            document.getElementById('edit_department_name').value = dept.department_name;
            document.getElementById('edit_description').value = dept.description || '';
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function deleteDepartment(deptId, deptName) {
            if (confirm('Are you sure you want to delete ' + deptName + '?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete">' +
                               '<input type="hidden" name="department_id" value="' + deptId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
