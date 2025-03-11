<?php
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
                    <li><a href="#"><span class="nav-icon courses-icon"></span>Courses</a></li>
                    <li><a href="#"><span class="nav-icon users-icon"></span>Users</a></li>
                    <li><a href="#"><span class="nav-icon stats-icon"></span>Statistics</a></li>
                    <li><a href="#"><span class="nav-icon settings-icon"></span>Settings</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.html" class="logout-btn">Sign Out</a>
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
                        <span class="admin-avatar">A</span>
                        <span class="admin-name">Admin</span>
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
                    
                    <button class="add-course-btn" id="addCourseBtn">Add New Course</button>
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
                                            <button class="edit-btn">Edit</button>
                                            <form method="POST" action="delete-course.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this course?');">
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
    
    <!-- Add Course Dialog -->
    <div class="modal-overlay" id="addCourseModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New Course</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="add-course.php" enctype="multipart/form-data" class="add-course-form">
                    <div class="form-group">
                        <label for="title">Course Title</label>
                        <input type="text" id="title" name="title" placeholder="Enter course title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Course Description</label>
                        <textarea id="description" name="description" placeholder="Enter course description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Course Image</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" required>
                            <div class="file-input-label">Choose a file</div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" id="cancelAdd">Cancel</button>
                        <button type="submit" class="submit-btn">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // simple JavaScript for modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const addCourseBtn = document.getElementById('addCourseBtn');
            const addCourseModal = document.getElementById('addCourseModal');
            const closeModal = document.getElementById('closeModal');
            const cancelAdd = document.getElementById('cancelAdd');
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.querySelector('.admin-container');
            
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
        });
    </script>
</body>
</html>

