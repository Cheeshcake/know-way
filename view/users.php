<?php
include '../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: index.html');
    exit;
}

// Check if user is an admin (role-based access control)
if ($_SESSION['role'] !== 'admin') {
    // Redirect to learner dashboard if not admin
    header('Location: dashboard.php');
    exit;
}

// Get username and initials for avatar
$username = $_SESSION['username'];
$initials = '';
$name_parts = explode(' ', $username);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Fetch user details for avatar (if needed)
$user_id = $_SESSION['user_id'];
$user_query = "SELECT avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user_avatar = null;
if ($result_user->num_rows === 1) {
    $user_data = $result_user->fetch_assoc();
    $user_avatar = $user_data['avatar'];
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the SQL query based on search and filter
$sql = "SELECT * FROM users WHERE ";

// Search condition
$sql .= "(username LIKE '%$search%' OR email LIKE '%$search%') ";

// Role filter
if ($filter !== 'all') {
    $sql .= "AND role = '$filter' ";
}

// Order by
$sql .= "ORDER BY created_at DESC ";

// Pagination
$sql .= "LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Count total users for pagination
$countSql = "SELECT COUNT(*) AS total FROM users WHERE ";
$countSql .= "(username LIKE '%$search%' OR email LIKE '%$search%') ";
if ($filter !== 'all') {
    $countSql .= "AND role = '$filter' ";
}
$total_users = $conn->query($countSql)->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Stats queries
$total_learners = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='learner'")->fetch_assoc()['count'];
$total_admins = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='admin'")->fetch_assoc()['count'];
$recent_users = $conn->query("SELECT COUNT(*) AS count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

// Process role update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $user_id = $_POST['user_id'] ?? 0;
    $new_role = $_POST['new_role'] ?? '';
    
    if ($user_id && $new_role) {
        $update_sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            // Redirect to prevent form resubmission
            header("Location: users.php?success=1");
            exit();
        } else {
            $error_message = "Failed to update user role.";
        }
    }
}

$success_message = isset($_GET['success']) && $_GET['success'] == 1 ? "User updated successfully!" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - User Management</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
</head>
<body>
    <div class="admin-container" id="adminContainer">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin.php"><span class="nav-icon dashboard-icon"></span>Dashboard</a></li>
                    <li><a href="#"><span class="nav-icon courses-icon"></span>Courses</a></li>
                    <li class="active"><a href="users.php"><span class="nav-icon users-icon"></span>Users</a></li>
                    <li><a href="#"><span class="nav-icon stats-icon"></span>Statistics</a></li>
                    <li><a href="settings.php"><span class="nav-icon settings-icon"></span>Settings</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../controller/logout.php" class="logout-btn">Sign Out</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h2>User Management</h2>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar">
                            <?php if (isset($user_avatar) && $user_avatar): ?>
                                <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                        </div>
                        <span class="admin-name"><?= htmlspecialchars($username) ?></span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <button class="close-alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                    <button class="close-alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <div class="toolbar">
                    <div class="search-filter">
                        <form method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="search-btn"></button>
                            </div>
                            
                            <div class="filter-wrapper">
                                <select name="filter" class="filter-select" onchange="this.form.submit()">
                                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Roles</option>
                                    <option value="admin" <?= $filter === 'admin' ? 'selected' : '' ?>>Admins</option>
                                    <option value="learner" <?= $filter === 'learner' ? 'selected' : '' ?>>Learners</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <p class="stat-number"><?= $total_users ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Learners</h3>
                        <p class="stat-number"><?= $total_learners ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Admins</h3>
                        <p class="stat-number"><?= $total_admins ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>New (Last 7 days)</h3>
                        <p class="stat-number"><?= $recent_users ?></p>
                    </div>
                </div>
                
                <div class="users-container">
                    <h3 class="section-title">User List</h3>
                    
                    <div class="users-table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($user = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="role-badge <?= $user['role'] === 'admin' ? 'admin' : 'learner' ?>">
                                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                            <td class="actions">
                                                <button class="edit-role-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= htmlspecialchars($user['username']) ?>" data-user-role="<?= htmlspecialchars($user['role']) ?>">
                                                    Edit Role
                                                </button>
                                                <form method="POST" action="../controller/delete-user.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="delete-btn">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="no-users">No users found matching your search criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>" class="pagination-btn prev">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>" class="pagination-number <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>" class="pagination-btn next">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Role Modal -->
    <div class="modal-overlay" id="editRoleModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit User Role</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="users.php" class="edit-role-form">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="form-group">
                        <label for="user_name">Username</label>
                        <input type="text" id="user_name" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_role">Role</label>
                        <select name="new_role" id="new_role" required>
                            <option value="learner">Learner</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" id="cancelEdit">Cancel</button>
                        <button type="submit" class="submit-btn">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            menuToggle.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // Edit Role Modal
            const editRoleModal = document.getElementById('editRoleModal');
            const closeModal = document.getElementById('closeModal');
            const cancelEdit = document.getElementById('cancelEdit');
            const editRoleBtns = document.querySelectorAll('.edit-role-btn');
            
            editRoleBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    const userRole = this.getAttribute('data-user-role');
                    
                    document.getElementById('edit_user_id').value = userId;
                    document.getElementById('user_name').value = userName;
                    document.getElementById('new_role').value = userRole;
                    
                    editRoleModal.classList.add('active');
                    document.body.classList.add('modal-open');
                });
            });
            
            function closeModalFunction() {
                editRoleModal.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
            
            closeModal.addEventListener('click', closeModalFunction);
            cancelEdit.addEventListener('click', closeModalFunction);
            
            editRoleModal.addEventListener('click', function(e) {
                if (e.target === editRoleModal) {
                    closeModalFunction();
                }
            });
            
            // Close alerts
            const closeAlerts = document.querySelectorAll('.close-alert');
            closeAlerts.forEach(function(closeAlert) {
                closeAlert.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                });
            });
        });
    </script>
    
    <style>
        /* Users table styles */
        .users-table-container {
            overflow-x: auto;
            background-color: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-lg);
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th,
        .users-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .users-table th {
            background-color: var(--off-white);
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .users-table tbody tr:hover {
            background-color: var(--off-white);
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-badge.admin {
            background-color: rgba(114, 9, 183, 0.15);
            color: var(--accent);
        }
        
        .role-badge.learner {
            background-color: rgba(67, 97, 238, 0.15);
            color: var(--primary);
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .edit-role-btn {
            padding: 6px 12px;
            background-color: var(--primary-light);
            color: var(--white);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            transition: background-color var(--transition-fast);
        }
        
        .edit-role-btn:hover {
            background-color: var(--primary);
        }
        
        .delete-btn {
            padding: 6px 12px;
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--red);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            transition: background-color var(--transition-fast);
        }
        
        .delete-btn:hover {
            background-color: var(--red);
            color: var(--white);
        }
        
        .no-users {
            text-align: center;
            padding: 24px;
            color: var(--gray);
        }
        
        /* Modal styles are already in admin-styles.css */
        
        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
                gap: 4px;
            }
            
            .edit-role-btn,
            .delete-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</body>
</html> 