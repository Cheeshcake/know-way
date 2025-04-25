<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: index.html');
    exit;
}

// Check if user is a learner (role-based access control)
if ($_SESSION['role'] !== 'learner') {
    // Redirect to appropriate dashboard based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        // For any unknown role, redirect to login
        header('Location: index.html');
    }
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get avatar path and initials
$user_avatar = '';
$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avatar_result = $stmt->get_result();
if ($avatar_result && $avatar_result->num_rows > 0) {
    $user_avatar = $avatar_result->fetch_assoc()['avatar'];
}
$stmt->close();

// Generate initials from username
$initials = '';
$name_parts = explode(' ', $username);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}
if (empty($initials)) {
    $initials = substr($username, 0, 1);
}

// Get user's actual progress data from database
$enrolled_courses_query = "SELECT COUNT(DISTINCT cl.course_id) as enrolled 
                          FROM course_likes cl 
                          WHERE cl.user_id = ?";
$stmt = $conn->prepare($enrolled_courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_result = $stmt->get_result();
$enrolled_count = $enrolled_result->fetch_assoc()['enrolled'];

// Get quiz attempts by the user
$quiz_attempts_query = "SELECT COUNT(*) as attempts 
                       FROM quiz_attempts 
                       WHERE user_id = ?";
$stmt = $conn->prepare($quiz_attempts_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attempts_result = $stmt->get_result();
$quiz_attempts = $attempts_result->fetch_assoc()['attempts'];

// Calculate completion percentage (based on quiz attempts)
$completion_query = "SELECT 
                    (SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND completed = 1) as completed,
                    (SELECT COUNT(*) FROM course_quizzes cq 
                     JOIN courses c ON c.id = cq.course_id 
                     JOIN course_likes cl ON cl.course_id = c.id 
                     WHERE cl.user_id = ?) as total";
$stmt = $conn->prepare($completion_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$completion_result = $stmt->get_result();
$completion_data = $completion_result->fetch_assoc();
$completed_quizzes = $completion_data['completed'];
$total_quizzes = $completion_data['total'];
$completion_percentage = $total_quizzes > 0 ? round(($completed_quizzes / $total_quizzes) * 100) : 0;

// Get latest interacted course
$latest_course_query = "SELECT c.title 
                       FROM courses c 
                       JOIN course_likes cl ON c.id = cl.course_id 
                       WHERE cl.user_id = ? 
                       ORDER BY cl.created_at DESC 
                       LIMIT 1";
$stmt = $conn->prepare($latest_course_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latest_course_result = $stmt->get_result();
$latest_course = $latest_course_result->num_rows > 0 ? $latest_course_result->fetch_assoc()['title'] : 'No courses yet';

// Get available published courses
$courses_sql = "SELECT * FROM courses WHERE status = 'published' ORDER BY created_at DESC LIMIT 6";
$courses_result = $conn->query($courses_sql);

// User progress data
$user_progress = [
    'courses_enrolled' => $enrolled_count,
    'courses_completed' => $completed_quizzes,
    'quiz_attempts' => $quiz_attempts
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Learner Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo">
                <img src="assets/logo-white-bg.png" alt="KnowWay Logo" class="logo-image">
                <div class="logo-text">KnowWay</div>
            </a>
            <button class="mobile-toggle" id="closeSidebar">
                &times;
            </button>
        </div>
        
        <div class="sidebar-content">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <ul class="nav-links">
                    <li>
                        <a href="dashboard.php" class="nav-link active">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="my-courses.php" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                <line x1="8" y1="6" x2="15" y2="6"></line>
                                <line x1="8" y1="10" x2="15" y2="10"></line>
                                <line x1="8" y1="14" x2="11" y2="14"></line>
                            </svg>
                            My Courses
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Learning</div>
                <ul class="nav-links">
                    
                    <li>
                        <a href="courses.php" class="nav-link">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            Courses
                        </a>
                    </li>
                   <li>
                    <a href="game-center.php" class="nav-link">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M6 12h.01"></path>
                            <path d="M18 12h.01"></path>
                            <path d="M12 6v.01"></path>
                            <path d="M12 18v.01"></path>
                        </svg>
                        Game Center
                    </a>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <ul class="nav-links">
                    
                    <li>
                        <a href="user-settings.php" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-weight: 600; flex-shrink: 0; text-align: center;">
                    <?php if (!empty($user_avatar) && file_exists($user_avatar)): ?>
                        <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; display: block; margin: 0; padding: 0;">
                    <?php else: ?>
                        <span style="font-size: 16px;"><?php echo htmlspecialchars($initials); ?></span>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $username; ?></div>
                    <div class="user-role">Learner</div>
                </div>
            </div>
            <a href="../controller/logout.php" class="logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="greeting">Welcome back, <?php echo $username; ?>!</h1>
            <p class="subheading">Track your progress and continue learning</p>
            </div>
        
        <div class="dashboard-grid">
            <!-- Progress Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">My Progress</h2>
                    </div>
                <div class="card-body">
                    <div class="progress-stats">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $user_progress['courses_enrolled']; ?></div>
                            <div class="stat-label">Courses Enrolled</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $user_progress['quiz_attempts']; ?></div>
                            <div class="stat-label">Quiz Attempts</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $completion_percentage; ?>%</div>
                            <div class="stat-label">Overall Progress</div>
                        </div>
                    </div>
                    
                        
                        </div>
                        </div>
                        
            <!-- Recommended Courses Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recommended Courses</h2>
                    <a href="courses.php" class="card-link">Browse All</a>
                </div>
                <div class="card-body">
                    <div class="courses-grid">
                        <?php
                        if ($courses_result && $courses_result->num_rows > 0) {
                            while($course = $courses_result->fetch_assoc()) {
                                // Get number of quizzes for this course
                                $quiz_count_query = "SELECT COUNT(*) as count FROM course_quizzes WHERE course_id = {$course['id']}";
                                $quiz_count_result = $conn->query($quiz_count_query);
                                $quiz_count = $quiz_count_result->fetch_assoc()['count'];
                                
                                echo '
                                <div class="course-card">
                                    <div class="course-image">
                                        <img src="uploads/' . ($course['image'] ?? 'assets/images/course-placeholder.jpg') . '" alt="' . $course['title'] . '" onerror="this.src=\'assets/images/course-placeholder.jpg\'">
                                    </div>
                                    <div class="course-content">
                                        <h3 class="course-title">' . $course['title'] . '</h3>
                                        <div class="course-info">
                                            <span>' . $quiz_count . ' Quizzes</span>
                                            <a href="course-details.php?id=' . $course['id'] . '" class="enroll-btn">Start</a>
                                        </div>
                                    </div>
                                </div>
                                ';
                            }
                        } else {
                            echo '<p>No courses available at the moment.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            
                        </div>
    </main>
    
    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        mobileMenuBtn.addEventListener('click', toggleSidebar);
        closeSidebarBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        }
        
        // Close sidebar on window resize if in mobile view
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        });
    </script>
</body>
</html> 