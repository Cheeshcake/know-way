<?php
// admin-courses.php - Admin Courses Management Page

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

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// In a real application, this would be a database query
// For this example, we'll use sample data
$coursesData = [
    [
        'id' => 1,
        'title' => 'Introduction to JavaScript',
        'category' => 'JavaScript',
        'instructor' => 'Sarah Johnson',
        'students' => 245,
        'rating' => 4.8,
        'status' => 'active',
        'created_at' => '15/01/2023'
    ],
    [
        'id' => 2,
        'title' => 'Advanced UX/UI Design',
        'category' => 'Design',
        'instructor' => 'Michael Chen',
        'students' => 198,
        'rating' => 4.7,
        'status' => 'active',
        'created_at' => '22/02/2023'
    ],
    [
        'id' => 3,
        'title' => 'Python for Beginners',
        'category' => 'Python',
        'instructor' => 'Emma Rodriguez',
        'students' => 176,
        'rating' => 4.6,
        'status' => 'active',
        'created_at' => '10/03/2023'
    ],
    [
        'id' => 4,
        'title' => 'HTML5 Essentials',
        'category' => 'HTML',
        'instructor' => 'Thomas Dubois',
        'students' => 132,
        'rating' => 4.9,
        'status' => 'active',
        'created_at' => '05/04/2023'
    ],
    [
        'id' => 5,
        'title' => 'CSS Mastery',
        'category' => 'CSS',
        'instructor' => 'Sophie Martin',
        'students' => 154,
        'rating' => 4.7,
        'status' => 'active',
        'created_at' => '18/04/2023'
    ],
    [
        'id' => 6,
        'title' => 'PHP Object-Oriented Programming',
        'category' => 'PHP',
        'instructor' => 'Lucas Bernard',
        'students' => 98,
        'rating' => 4.8,
        'status' => 'active',
        'created_at' => '25/04/2023'
    ],
    [
        'id' => 7,
        'title' => 'React Fundamentals',
        'category' => 'JavaScript',
        'instructor' => 'Chloé Petit',
        'students' => 187,
        'rating' => 4.8,
        'status' => 'active',
        'created_at' => '02/05/2023'
    ],
    [
        'id' => 8,
        'title' => 'Responsive Web Design',
        'category' => 'CSS',
        'instructor' => 'Ahmed Hassan',
        'students' => 154,
        'rating' => 4.7,
        'status' => 'inactive',
        'created_at' => '15/05/2023'
    ]
];

// Filter courses based on search and filter parameters
if ($search || $filter) {
    $filteredCourses = array_filter($coursesData, function($course) use ($search, $filter) {
        $matchesSearch = empty($search) || 
                        stripos($course['title'], $search) !== false || 
                        stripos($course['category'], $search) !== false ||
                        stripos($course['instructor'], $search) !== false;
        
        $matchesFilter = empty($filter) || 
                        ($filter === 'active' && $course['status'] === 'active') ||
                        ($filter === 'inactive' && $course['status'] === 'inactive');
        
        return $matchesSearch && $matchesFilter;
    });
} else {
    $filteredCourses = $coursesData;
}

// Pagination
$totalCourses = count($filteredCourses);
$totalPages = ceil($totalCourses / $limit);

// Get paginated courses
$paginatedCourses = array_slice($filteredCourses, $offset, $limit);

// Get admin initials
$admin_initials = 'A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - Course Management</title>
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
                    <li class="active"><a href="admin-courses.php"><i class="fas fa-book"></i>Courses</a></li>
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
                    <h2>Course Management</h2>
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
                                <input type="text" name="search" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                            
                            <div class="filter-wrapper">
                                <select name="filter" class="filter-select">
                                    <option value="" <?php echo $filter === '' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <button class="add-course-btn" id="addCourseBtn"><i class="fas fa-plus"></i> Add New Course</button>
                </div>
                
                <div class="courses-table-container">
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Instructor</th>
                                <th>Students</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($paginatedCourses) > 0): ?>
                                <?php foreach ($paginatedCourses as $course): ?>
                                    <tr>
                                        <td>
                                            <div class="course-info">
                                                <div class="course-icon">
                                                    <?php 
                                                    $initials = substr($course['title'], 0, 1);
                                                    echo htmlspecialchars($initials);
                                                    ?>
                                                </div>
                                                <span class="course-title"><?php echo htmlspecialchars($course['title']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($course['category']); ?></td>
                                        <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                        <td><?php echo $course['students']; ?></td>
                                        <td>
                                            <div class="rating">
                                                <?php echo $course['rating']; ?>
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $course['status']; ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $course['created_at']; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                                <a href="edit-course.php?id=<?php echo $course['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                                <button class="action-btn delete-btn" onclick="confirmDeleteCourse(<?php echo $course['id']; ?>)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No courses found. Try adjusting your search or filters.</td>
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
    
    <!-- Add Course Modal -->
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
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="HTML">HTML</option>
                                <option value="CSS">CSS</option>
                                <option value="JavaScript">JavaScript</option>
                                <option value="PHP">PHP</option>
                                <option value="Python">Python</option>
                                <option value="Design">Design</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="instructor">Instructor</label>
                            <select id="instructor" name="instructor" required>
                                <option value="">Select an instructor</option>
                                <option value="Sarah Johnson">Sarah Johnson</option>
                                <option value="Michael Chen">Michael Chen</option>
                                <option value="Emma Rodriguez">Emma Rodriguez</option>
                                <option value="Thomas Dubois">Thomas Dubois</option>
                                <option value="Sophie Martin">Sophie Martin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Course Image</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" required>
                            <div class="file-input-label">Choose a file</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="status" value="active" checked>
                                <span>Active</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="status" value="inactive">
                                <span>Inactive</span>
                            </label>
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
        <input type="hidden" name="id" id="deleteCourseId">
    </form>
    
    <script>
        function confirmDeleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                document.getElementById('deleteCourseId').value = courseId;
                document.getElementById('deleteCourseForm').submit();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // Add course modal
            const addCourseBtn = document.getElementById('addCourseBtn');
            const addCourseModal = document.getElementById('addCourseModal');
            const closeModal = document.getElementById('closeModal');
            const cancelAdd = document.getElementById('cancelAdd');
            
            if (addCourseBtn && addCourseModal) {
                addCourseBtn.addEventListener('click', function() {
                    addCourseModal.classList.add('active');
                    document.body.classList.add('modal-open');
                });
                
                function closeModalFunction() {
                    addCourseModal.classList.remove('active');
                    document.body.classList.remove('modal-open');
                }
                
                if (closeModal) {
                    closeModal.addEventListener('click', closeModalFunction);
                }
                
                if (cancelAdd) {
                    cancelAdd.addEventListener('click', closeModalFunction);
                }
                
                addCourseModal.addEventListener('click', function(e) {
                    if (e.target === addCourseModal) {
                        closeModalFunction();
                    }
                });
            }
            
            // File input
            const fileInput = document.getElementById('image');
            const fileLabel = document.querySelector('.file-input-label');
            
            if (fileInput && fileLabel) {
                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length > 0) {
                        fileLabel.textContent = fileInput.files[0].name;
                    } else {
                        fileLabel.textContent = 'Choose a file';
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
        });
    </script>
</body>
</html>
