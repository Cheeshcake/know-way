<?php
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

// Store session username in a different variable to avoid conflicts
$user_session_name = $_SESSION['username'];
$initials = '';
$name_parts = explode(' ', $user_session_name);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Include database - note this defines its own $username for DB connection
include '../config/db.php';

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
$filter = $_GET['filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 3;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM courses WHERE title LIKE '%$search%'";

if ($filter) {
    $sql .= " ORDER BY created_at " . ($filter === 'newest' ? "DESC" : "ASC");
} else {
    $sql .= " ORDER BY created_at DESC";
}

$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$total_courses = $conn->query("SELECT COUNT(*) AS total FROM courses WHERE title LIKE '%$search%'")->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $limit);

$isLoading = false; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - Dashboard</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="admin.php"><span class="nav-icon dashboard-icon"></span>Dashboard</a></li>
                    <li><a href="pending-courses.php"><span class="nav-icon courses-icon"></span>Pending Courses</a></li>
                    <li><a href="users.php"><span class="nav-icon users-icon"></span>Users</a></li>
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
                    <h2>Course Management</h2>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-weight: 600; flex-shrink: 0; text-align: center;">
                            <?php if (isset($user_avatar) && $user_avatar): ?>
                                <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; display: block; margin: 0; padding: 0;">
                            <?php else: ?>
                                <span style="font-size: 16px;"><?php echo htmlspecialchars($initials); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="admin-name"><?= htmlspecialchars($user_session_name) ?></span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <div class="toolbar">
                    <div class="search-filter">
                        <form method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <input type="text" name="search" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="search-btn"></button>
                            </div>
                            
                            <div class="filter-wrapper">
                                <select name="filter" class="filter-select">
                                    <option value="">Sort by</option>
                                    <option value="newest" <?= $filter === 'newest' ? 'selected' : '' ?>>Newest</option>
                                    <option value="oldest" <?= $filter === 'oldest' ? 'selected' : '' ?>>Oldest</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <a href="create-course.php" class="add-course-btn">
                        <span class="add-icon"></span>
                        Add New Course
                    </a>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Courses</h3>
                        <p class="stat-number"><?= $total_courses ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Users</h3>
                        <p class="stat-number">217</p>
                    </div>
                    <div class="stat-card">
                        <h3>Course Completions</h3>
                        <p class="stat-number">548</p>
                    </div>
                    <div class="stat-card">
                        <h3>Average Rating</h3>
                        <p class="stat-number">4.8</p>
                    </div>
                </div>
                
                <div class="courses-container">
                    <h3 class="section-title">Course Catalog</h3>
                    
                    <?php if ($isLoading): ?>
                    <!-- Skeleton Loaders -->
                    <div class="courses-grid">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                        <div class="course-card skeleton">
                            <div class="skeleton-img"></div>
                            <div class="skeleton-title"></div>
                            <div class="skeleton-desc"></div>
                            <div class="skeleton-btn"></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php else: ?>
                    
                    <div class="courses-grid">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="course-card">
                                    <div class="course-img">
                                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" onerror="this.src='placeholder-course.png'">
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title"><?= htmlspecialchars($row['title']) ?></h4>
                                        <p class="course-desc"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</p>
                                        <div class="course-actions">
                                        <button class="edit-btn" onclick="window.location.href='course-details.php?id=<?= $row['id'] ?>'">Preview</button>
                                            <form method="POST" action="../controller/delete-course.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-courses">
                                <p>No courses found. Add your first course!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
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
    
    <script>
        // simple JavaScript for sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.querySelector('.admin-container');
            
            menuToggle.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
    
    <style>
        /* ... existing CSS styles ... */
        
        .add-course-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #4f46e5;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
        }
        
        .add-course-btn:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
        }
        
        .add-icon {
            display: inline-block;
            width: 18px;
            height: 18px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        /* ... more CSS styles ... */
    </style>
</body>
</html>

