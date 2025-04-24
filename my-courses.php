<?php
// my-courses.php - User Courses Page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For demonstration purposes, set admin status
// In a real application, this would come from database
$_SESSION['role'] = true;

// User data retrieval (replace with your database code)
$user = [
    'id' => 1,
    'name' => 'Nourane abdella',
    'email' => 'Nourane.abdella@example.com',
    'avatar' => null,
    'enrolled_courses' => [
        [
            'id' => 1,
            'title' => 'Introduction to Web Development',
            'category' => 'HTML',
            'progress' => 75,
            'last_activity' => '18/04/2025',
            'image' => 'course-web-dev.jpg'
        ],
        [
            'id' => 2,
            'title' => 'Advanced Digital Marketing',
            'category' => 'Marketing',
            'progress' => 45,
            'last_activity' => '10/03/2025',
            'image' => 'course-marketing.jpg'
        ],
        [
            'id' => 3,
            'title' => 'Photography for Beginners',
            'category' => 'Photography',
            'progress' => 90,
            'last_activity' => '04/10/2025',
            'image' => 'course-photography.jpg'
        ],
        [
            'id' => 4,
            'title' => 'CSS Fundamentals',
            'category' => 'CSS',
            'progress' => 60,
            'last_activity' => '15/04/2025',
            'image' => 'course-css.jpg'
        ],
        [
            'id' => 5,
            'title' => 'PHP for Beginners',
            'category' => 'PHP',
            'progress' => 30,
            'last_activity' => '20/03/2025',
            'image' => 'course-php.jpg'
        ]
    ],
    'recommended_courses' => [
        [
            'id' => 6,
            'title' => 'CSS Mastery: Advanced Styling',
            'category' => 'CSS',
            'instructor' => 'Sarah Johnson',
            'rating' => 4.8,
            'students' => 1245,
            'image' => 'course-css.jpg'
        ],
        [
            'id' => 7,
            'title' => 'PHP Object-Oriented Programming',
            'category' => 'PHP',
            'instructor' => 'Michael Chen',
            'rating' => 4.6,
            'students' => 987,
            'image' => 'course-php.jpg'
        ],
        [
            'id' => 8,
            'title' => 'HTML5 Fundamentals',
            'category' => 'HTML',
            'instructor' => 'Emma Rodriguez',
            'rating' => 4.9,
            'students' => 2156,
            'image' => 'course-html.jpg'
        ]
    ]
];

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $user['name']);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Get active category from URL parameter
$activeCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - My Courses</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="my-courses.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container" id="adminContainer">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Student Dashboard</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li class="active"><a href="my-courses.php"><i class="fas fa-book"></i>My Courses</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                    <li><a href="quiz.php"><i class="fas fa-question-circle"></i>Quiz</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="admin.php"><i class="fas fa-user-shield"></i>Admin Panel</a></li>
                    <?php endif; ?>
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
                    <h2>My Courses</h2>
                </div>
                
                <div class="header-right">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php if ($user['avatar']): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <!-- My Courses Section -->
                <section class="courses-section">
                    <div class="section-header">
                        <h3>My Enrolled Courses</h3>
                        <div class="section-actions">
                            <div class="search-container">
                                <input type="text" placeholder="Search courses..." class="search-input">
                                <button class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                            <div class="view-options">
                                <button class="view-btn active" data-view="grid"><i class="fas fa-th-large"></i></button>
                                <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category Filter Tabs -->
                    <div class="category-tabs">
                        <a href="my-courses.php" class="category-tab <?php echo $activeCategory === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="my-courses.php?category=HTML" class="category-tab <?php echo $activeCategory === 'HTML' ? 'active' : ''; ?>">HTML</a>
                        <a href="my-courses.php?category=CSS" class="category-tab <?php echo $activeCategory === 'CSS' ? 'active' : ''; ?>">CSS</a>
                        <a href="my-courses.php?category=PHP" class="category-tab <?php echo $activeCategory === 'PHP' ? 'active' : ''; ?>">PHP</a>
                    </div>
                    
                    <div class="courses-grid" id="enrolledCourses">
                        <?php 
                        $filteredCourses = $user['enrolled_courses'];
                        if ($activeCategory !== 'all') {
                            $filteredCourses = array_filter($user['enrolled_courses'], function($course) use ($activeCategory) {
                                return $course['category'] === $activeCategory;
                            });
                        }
                        
                        foreach ($filteredCourses as $course): 
                        ?>
                            <div class="course-card">
                                <div class="course-image">
                                    <?php if (isset($course['image'])): ?>
                                        <img src="placeholder-course.png" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <?php else: ?>
                                        <div class="course-image-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="course-category"><?php echo htmlspecialchars($course['category']); ?></div>
                                </div>
                                <div class="course-content">
                                    <h4 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                    <p class="course-meta">Last activity: <?php echo htmlspecialchars($course['last_activity']); ?></p>
                                    
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo $course['progress']; ?>% completed</span>
                                    </div>
                                    
                                    <div class="course-actions">
                                        <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Continue Learning</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($filteredCourses) === 0): ?>
                            <div class="no-courses-message">
                                <i class="fas fa-info-circle"></i>
                                <p>No courses found in this category. <a href="my-courses.php">View all courses</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Recommended Courses Section -->
                <section class="courses-section recommended-section">
                    <div class="section-header">
                        <h3>Recommended Courses</h3>
                        <div class="section-actions">
                            <a href="browse-courses.php" class="btn btn-outline">View All</a>
                        </div>
                    </div>
                    
                    <div class="courses-grid" id="recommendedCourses">
                        <?php foreach ($user['recommended_courses'] as $course): ?>
                            <div class="course-card recommended">
                                <div class="course-image">
                                    <?php if (isset($course['image'])): ?>
                                        <img src="placeholder-course.png" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <?php else: ?>
                                        <div class="course-image-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="course-category"><?php echo htmlspecialchars($course['category']); ?></div>
                                </div>
                                <div class="course-content">
                                    <h4 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                    <p class="course-instructor">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor']); ?>
                                    </p>
                                    <div class="course-stats">
                                        <span class="course-rating">
                                            <i class="fas fa-star"></i> <?php echo htmlspecialchars($course['rating']); ?>
                                        </span>
                                        <span class="course-students">
                                            <i class="fas fa-users"></i> <?php echo htmlspecialchars($course['students']); ?> students
                                        </span>
                                    </div>
                                    
                                    <div class="course-actions">
                                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline">View Details</a>
                                        <a href="enroll.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Enroll Now</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // Toggle view (grid/list)
            const viewButtons = document.querySelectorAll('.view-btn');
            const coursesGrid = document.getElementById('enrolledCourses');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Change view based on data-view attribute
                    const viewType = this.getAttribute('data-view');
                    if (viewType === 'list') {
                        coursesGrid.classList.add('courses-list-view');
                    } else {
                        coursesGrid.classList.remove('courses-list-view');
                    }
                });
            });
            
            // Search functionality
            const searchInput = document.querySelector('.search-input');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const courseCards = document.querySelectorAll('#enrolledCourses .course-card');
                
                courseCards.forEach(card => {
                    const title = card.querySelector('.course-title').textContent.toLowerCase();
                    const category = card.querySelector('.course-category').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || category.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
