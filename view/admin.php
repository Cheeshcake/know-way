<?php
// admin.php - Admin Dashboard (English version)

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

include 'db.php';
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
$total_courses = 24;
$total_pages = ceil($total_courses / $limit);

$isLoading = false;

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_course') {
        // Process the form submission (in a real app, this would save to database)
        
        // Redirect with success message
        header('Location: admin.php?success=1');
        exit;
    }
}

$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - Dashboard</title>
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
                    <li class="active"><a href="admin.php"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li><a href="admin-courses.php"><i class="fas fa-book"></i>Courses</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i>Users</a></li>
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
                    <h2>Dashboard</h2>
                </div>
                
                <div class="header-right">
                    <div class="user-profile">
                        <div class="user-avatar">A</div>
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php 
                $successMessage = isset($_GET['success']) ? $_GET['success'] : 'Your changes have been successfully saved.';
                if (isset($_GET['success'])): 
                ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button class="close-alert"><i class="fas fa-times"></i></button>
                </div>
                <?php endif; ?>
                
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
                    <div class="toolbar">
                        <div class="search-filter">
                            <form method="GET" class="search-form">
                                <div class="search-input-wrapper">
                                    <input type="text" name="search" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
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
                        
                        <button class="add-course-btn" id="addCourseBtn"><i class="fas fa-plus"></i> Add New Course</button>
                    </div>
                    
                    <h3 class="section-title">Recent Courses</h3>
                    
                    <?php if ($isLoading): ?>
                    <!-- Skeleton Loaders -->
                    <div class="courses-grid">
                        <?php for ($i = 0; $i < 3; $i++): ?>
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
                        <div class="course-card">
                            <div class="course-img">
                                <img src="placeholder-course.png" alt="Introduction to JavaScript">
                            </div>
                            <div class="course-content">
                                <h4 class="course-title">Introduction to JavaScript</h4>
                                <p class="course-desc">Learn the basics of JavaScript, the programming language of the web...</p>
                                <div class="course-actions">
                                    <button class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="delete-btn" onclick="confirmDelete(1)"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="course-card">
                            <div class="course-img">
                                <img src="placeholder-course.png" alt="Advanced UX/UI Design">
                            </div>
                            <div class="course-content">
                                <h4 class="course-title">Advanced UX/UI Design</h4>
                                <p class="course-desc">Master advanced techniques for user interface design...</p>
                                <div class="course-actions">
                                    <button class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="delete-btn" onclick="confirmDelete(2)"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="course-card">
                            <div class="course-img">
                                <img src="placeholder-course.png" alt="Python for Beginners">
                            </div>
                            <div class="course-content">
                                <h4 class="course-title">Python for Beginners</h4>
                                <p class="course-desc">Discover Python, a powerful and easy-to-learn programming language...</p>
                                <div class="course-actions">
                                    <button class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="delete-btn" onclick="confirmDelete(3)"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Navigation links to other pages -->
                <div class="admin-navigation-links">
                    <h3 class="section-title">Quick Links</h3>
                    <div class="quick-links-grid">
                        <a href="my-courses.php" class="quick-link-card">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Student Dashboard</span>
                        </a>
                        <a href="settings.php" class="quick-link-card">
                            <i class="fas fa-cog"></i>
                            <span>User Settings</span>
                        </a>
                        <a href="admin-courses.php" class="quick-link-card">
                            <i class="fas fa-book"></i>
                            <span>Manage Courses</span>
                        </a>
                        <a href="users.php" class="quick-link-card">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Course Dialog -->
    <div class="modal-overlay" id="addCourseModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New Course</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="add-course.php" enctype="multipart/form-data" class="settings-form">
                    
                    <div class="form-group">
                        <label for="title">Course Title</label>
                        <input type="text" id="title" name="title" placeholder="Enter course title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Course Description</label>
                        <textarea id="description" name="description" placeholder="Enter course description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Course Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="HTML">HTML</option>
                            <option value="CSS">CSS</option>
                            <option value="PHP">PHP</option>
                            <option value="JavaScript">JavaScript</option>
                            <option value="Design">Design</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Course Image</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" required>
                            <div class="file-input-label">Choose a file</div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAdd">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Course Form (Hidden) -->
    <form id="deleteCourseForm" method="POST" action="delete-course.php" style="display: none;">
        <input type="hidden" name="id" id="deleteId">
    </form>
    
    <script>
        function confirmDelete(courseId) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                document.getElementById('deleteId').value = courseId;
                document.getElementById('deleteCourseForm').submit();
            }
        }
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addCourseBtn = document.getElementById('addCourseBtn');
            const addCourseModal = document.getElementById('addCourseModal');
            const closeModal = document.getElementById('closeModal');
            const cancelAdd = document.getElementById('cancelAdd');
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            addCourseBtn.addEventListener('click', function() {
                addCourseModal.classList.add('active');
                document.body.classList.add('modal-open');
            });
            
            function closeModalFunction() {
                addCourseModal.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
            
            closeModal.addEventListener('click', closeModalFunction);
            cancelAdd.addEventListener('click', closeModalFunction);
            
            addCourseModal.addEventListener('click', function(e) {
                if (e.target === addCourseModal) {
                    closeModalFunction();
                }
            });
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            const fileInput = document.getElementById('image');
            const fileLabel = document.querySelector('.file-input-label');
            
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileLabel.textContent = fileInput.files[0].name;
                } else {
                    fileLabel.textContent = 'Choose a file';
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
</body>
</html>
