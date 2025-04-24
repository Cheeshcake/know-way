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

// Get available courses
$courses_sql = "SELECT * FROM courses ORDER BY created_at DESC LIMIT 6";
$courses_result = $conn->query($courses_sql);

// Get user's progress (in a real application, this would come from a user_courses table)
// For demo purposes, we'll just simulate progress
$user_progress = [
    'courses_enrolled' => 3,
    'courses_completed' => 1,
    'current_course' => 'Introduction to Web Development',
    'next_lesson' => 'CSS Flexbox and Grid Layout'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Learner Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="user-welcome">
                <div class="user-avatar">
                    <?php echo substr($username, 0, 1); ?>
                </div>
                <div class="welcome-text">
                    <h1>Welcome back, <?php echo htmlspecialchars($username); ?></h1>
                    <p>Continue your learning journey where you left off</p>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <button class="action-btn outline">Browse Courses</button>
                <button class="action-btn">Continue Learning</button>
            </div>
        </header>
        
        <div class="dashboard-grid">
            <div class="main-content">
                <div class="progress-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Your Learning Progress</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    
                    <div class="progress-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $user_progress['courses_enrolled']; ?></div>
                            <div class="stat-label">Courses Enrolled</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $user_progress['courses_completed']; ?></div>
                            <div class="stat-label">Courses Completed</div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-number">24</div>
                            <div class="stat-label">Hours Learning</div>
                        </div>
                    </div>
                    
                    <div class="progress-current">
                        <h4><?php echo htmlspecialchars($user_progress['current_course']); ?></h4>
                        
                        <div class="progress-bar-container">
                            <div class="progress-bar"></div>
                        </div>
                        
                        <div class="progress-info">
                            <span>75% Complete</span>
                            <span>Lesson 6 of 8</span>
                        </div>
                        
                        <div class="next-lesson">
                            <p>Next Lesson:</p>
                            <a href="#"><?php echo htmlspecialchars($user_progress['next_lesson']); ?></a>
                        </div>
                    </div>
                </div>
                
                <div class="courses-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Recommended Courses</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    
                    <div class="courses-grid">
                        <?php if ($courses_result->num_rows > 0): ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <div class="course-card">
                                    <div class="course-img">
                                        <img src="uploads/<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" onerror="this.src='placeholder-course.png'">
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title"><?= htmlspecialchars($course['title']) ?></h4>
                                        <div class="course-actions">
                                            <button class="enroll-btn">Enroll Now</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No courses available at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="profile-panel">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <?php echo substr($username, 0, 1); ?>
                        </div>
                        <h3 class="profile-name"><?php echo htmlspecialchars($username); ?></h3>
                        <p class="profile-email"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                        <span class="profile-status">Learner</span>
                    </div>
                    
                    <div class="profile-links">
                        <a href="#" class="profile-link">My Profile</a>
                        <a href="#" class="profile-link">My Courses</a>
                        <a href="#" class="profile-link">Certificates</a>
                        <a href="#" class="profile-link">Settings</a>
                        <a href="../controller/logout.php" class="logout-btn">Sign Out</a>
                    </div>
                </div>
                
                <div class="schedule-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Upcoming Schedule</h3>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-time">Today, 10:00 AM</div>
                        <div class="schedule-title">Web Development Workshop</div>
                        <div class="schedule-desc">Learn the fundamentals of web development and build your first website.</div>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-time">Tomorrow, 2:00 PM</div>
                        <div class="schedule-title">Design Principles</div>
                        <div class="schedule-desc">Explore essential design principles for creating effective user interfaces.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 