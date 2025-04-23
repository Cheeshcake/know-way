<?php
// users.php - Admin Users Management Page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect non-admin users
    header('Location: dashboard.php');
    exit;
}

// Connexion à la base de données (si nécessaire)
include 'db.php';

// Récupération des paramètres de recherche et filtrage
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Requête pour récupérer les utilisateurs
$sql = "SELECT * FROM users WHERE 
        (name LIKE '%$search%' OR email LIKE '%$search%')";

if ($filter !== 'all') {
    $sql .= " AND status = '$filter'";
}

$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";

// Exécution de la requête (commentée pour l'exemple)
// $result = $conn->query($sql);

// Données fictives pour l'exemple
$usersData = [
    [
        'id' => 1,
        'name' => 'Sophie Martin',
        'email' => 'sophie.martin@example.com',
        'status' => 'active',
        'enrolledCourses' => 3,
        'lastActive' => '15/04/2023',
    ],
    [
        'id' => 2,
        'name' => 'Thomas Dubois',
        'email' => 'thomas.dubois@example.com',
        'status' => 'active',
        'enrolledCourses' => 2,
        'lastActive' => '14/04/2023',
    ],
    [
        'id' => 3,
        'name' => 'Emma Leroy',
        'email' => 'emma.leroy@example.com',
        'status' => 'inactive',
        'enrolledCourses' => 1,
        'lastActive' => '20/03/2023',
    ],
    [
        'id' => 4,
        'name' => 'Lucas Bernard',
        'email' => 'lucas.bernard@example.com',
        'status' => 'active',
        'enrolledCourses' => 5,
        'lastActive' => '12/04/2023',
    ],
    [
        'id' => 5,
        'name' => 'Chloé Petit',
        'email' => 'chloe.petit@example.com',
        'status' => 'pending',
        'enrolledCourses' => 0,
        'lastActive' => '10/04/2023',
    ],
];

// Statistiques
$totalUsers = count($usersData);
$activeUsers = count(array_filter($usersData, function($user) { return $user['status'] === 'active'; }));
$inactiveUsers = count(array_filter($usersData, function($user) { return $user['status'] === 'inactive'; }));
$pendingUsers = count(array_filter($usersData, function($user) { return $user['status'] === 'pending'; }));

// Filtrage des utilisateurs selon les critères de recherche
if ($search || $filter !== 'all') {
    $filteredUsers = array_filter($usersData, function($user) use ($search, $filter) {
        $matchesSearch = empty($search) || 
                        stripos($user['name'], $search) !== false || 
                        stripos($user['email'], $search) !== false;
        
        $matchesFilter = $filter === 'all' || $user['status'] === $filter;
        
        return $matchesSearch && $matchesFilter;
    });
} else {
    $filteredUsers = $usersData;
}

// Pagination
$totalPages = ceil(count($filteredUsers) / $limit);

// Get initials for avatar placeholder
$admin_initials = 'A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - User Management</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li><a href="admin.php"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li><a href="admin-courses.php"><i class="fas fa-book"></i>Courses</a></li>
                    <li class="active"><a href="users.php"><i class="fas fa-users"></i>Users</a></li>
                    <li><a href="admin-statistics.php"><i class="fas fa-chart-bar"></i>Statistics</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                    <li><a href="dashboard.php"><i class="fas fa-graduation-cap"></i>Student Dashboard</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo htmlspecialchars($admin_initials); ?></div>
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button class="close-alert"><i class="fas fa-times"></i></button>
                </div>
                <?php endif; ?>
                
                <div class="toolbar">
                    <div class="search-filter">
                        <form method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                            
                            <div class="filter-wrapper">
                                <select name="filter" class="filter-select">
                                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <button class="add-user-btn" id="addUserBtn"><i class="fas fa-plus"></i> Add User</button>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <p class="stat-number"><?php echo $totalUsers; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Users</h3>
                        <p class="stat-number"><?php echo $activeUsers; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Inactive Users</h3>
                        <p class="stat-number"><?php echo $inactiveUsers; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Pending</h3>
                        <p class="stat-number"><?php echo $pendingUsers; ?></p>
                    </div>
                </div>
                
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Enrolled Courses</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($filteredUsers) > 0): ?>
                                <?php foreach ($filteredUsers as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php 
                                                    $initials = '';
                                                    $name_parts = explode(' ', $user['name']);
                                                    foreach ($name_parts as $part) {
                                                        $initials .= substr($part, 0, 1);
                                                    }
                                                    echo htmlspecialchars($initials);
                                                    ?>
                                                </div>
                                                <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                                <?php 
                                                    echo ucfirst($user['status']);
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['enrolledCourses']; ?></td>
                                        <td><?php echo $user['lastActive']; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="user-detail.php?id=<?php echo $user['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                                <button class="action-btn delete-btn" onclick="confirmDeleteUser(<?php echo $user['id']; ?>)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No users found. Try adjusting your search or filters.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="pagination-btn prev"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="pagination-btn next">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="add-user.php" class="settings-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select id="role" name="role" required>
                            <option value="student">Student</option>
                            <option value="instructor">Instructor</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAdd">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete User Form (Hidden) -->
    <form id="deleteUserForm" method="POST" action="delete-user.php" style="display: none;">
        <input type="hidden" name="id" id="deleteUserId">
    </form>
    
    <script>
        function confirmDeleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteUserForm').submit();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const addUserBtn = document.getElementById('addUserBtn');
            const addUserModal = document.getElementById('addUserModal');
            const closeModal = document.getElementById('closeModal');
            const cancelAdd = document.getElementById('cancelAdd');
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            // Toggle sidebar
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // Add user modal
            if (addUserBtn && addUserModal) {
                addUserBtn.addEventListener('click', function() {
                    addUserModal.classList.add('active');
                    document.body.classList.add('modal-open');
                });
                
                function closeModalFunction() {
                    addUserModal.classList.remove('active');
                    document.body.classList.remove('modal-open');
                }
                
                if (closeModal) {
                    closeModal.addEventListener('click', closeModalFunction);
                }
                
                if (cancelAdd) {
                    cancelAdd.addEventListener('click', closeModalFunction);
                }
                
                addUserModal.addEventListener('click', function(e) {
                    if (e.target === addUserModal) {
                        closeModalFunction();
                    }
                });
            }
            
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
            
            // Form validation
            const userForm = document.querySelector('form[action="add-user.php"]');
            if (userForm) {
                userForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                    }
                });
            }
        });
    </script>
</body>
</html>
