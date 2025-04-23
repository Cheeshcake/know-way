<?php
// dashboard.php - User Dashboard Page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For demonstration purposes, set admin status
// In a real application, this would come from database
$_SESSION['is_admin'] = true;

// User data retrieval (replace with your database code)
$user = [
    'id' => 1,
    'name' => 'Nourane abdella',
    'email' => 'Nourane.abdella@example.com',
    'avatar' => null,
    'joined_date' => '15/01/2025',
    'total_courses' => 5,
    'completed_courses' => 2,
    'total_hours' => 47,
    'certificates' => 2,
    'enrolled_courses' => [
        [
            'id' => 1,
            'title' => 'Introduction to Web Development',
            'category' => 'HTML',
            'progress' => 75,
            'last_activity' => '18/04/2025',
            'image' => 'course-web-dev.jpg',
            'next_lesson' => 'CSS Layouts and Positioning'
        ],
        [
            'id' => 2,
            'title' => 'Advanced Digital Marketing',
            'category' => 'Marketing',
            'progress' => 45,
            'last_activity' => '10/03/2025',
            'image' => 'course-marketing.jpg',
            'next_lesson' => 'Social Media Strategy'
        ],
        [
            'id' => 3,
            'title' => 'Photography for Beginners',
            'category' => 'Photography',
            'progress' => 90,
            'last_activity' => '04/10/2025',
            'image' => 'course-photography.jpg',
            'next_lesson' => 'Final Project Submission'
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
        ]
    ],
    'recent_activities' => [
        [
            'type' => 'lesson_completed',
            'course_id' => 1,
            'course_title' => 'Introduction to Web Development',
            'lesson' => 'HTML Forms and Validation',
            'date' => '18/04/2025',
            'time' => '14:30'
        ],
        [
            'type' => 'quiz_completed',
            'course_id' => 3,
            'course_title' => 'Photography for Beginners',
            'score' => 85,
            'date' => '17/04/2025',
            'time' => '10:15'
        ],
        [
            'type' => 'course_started',
            'course_id' => 2,
            'course_title' => 'Advanced Digital Marketing',
            'date' => '10/03/2025',
            'time' => '09:45'
        ],
        [
            'type' => 'certificate_earned',
            'course_id' => 4,
            'course_title' => 'Graphic Design Fundamentals',
            'date' => '01/03/2025',
            'time' => '16:20'
        ]
    ],
    'upcoming_events' => [
        [
            'type' => 'assignment',
            'title' => 'Submit Photography Portfolio',
            'course_id' => 3,
            'course_title' => 'Photography for Beginners',
            'due_date' => '25/04/2025'
        ],
        [
            'type' => 'live_session',
            'title' => 'Q&A with Marketing Expert',
            'course_id' => 2,
            'course_title' => 'Advanced Digital Marketing',
            'date' => '28/04/2025',
            'time' => '15:00'
        ],
        [
            'type' => 'exam',
            'title' => 'Final Web Development Exam',
            'course_id' => 1,
            'course_title' => 'Introduction to Web Development',
            'date' => '05/05/2025',
            'time' => '10:00'
        ]
    ],
    'learning_stats' => [
        'weekly_hours' => [
            'mon' => 2.5,
            'tue' => 1.0,
            'wed' => 3.0,
            'thu' => 2.0,
            'fri' => 1.5,
            'sat' => 4.0,
            'sun' => 0.5
        ],
        'monthly_completion' => [
            'jan' => 2,
            'feb' => 3,
            'mar' => 5,
            'apr' => 4
        ],
        'skills' => [
            ['name' => 'HTML', 'level' => 80],
            ['name' => 'CSS', 'level' => 65],
            ['name' => 'JavaScript', 'level' => 45],
            ['name' => 'Photography', 'level' => 90],
            ['name' => 'Marketing', 'level' => 50]
        ]
    ],
    'badges' => [
        [
            'id' => 1,
            'title' => 'Fast Learner',
            'description' => 'Completed 5 lessons in one day',
            'icon' => 'badge-fast-learner.png',
            'earned_date' => '10/03/2025'
        ],
        [
            'id' => 2,
            'title' => 'Perfect Score',
            'description' => 'Achieved 100% on a quiz',
            'icon' => 'badge-perfect-score.png',
            'earned_date' => '05/02/2025'
        ],
        [
            'id' => 3,
            'title' => 'Early Bird',
            'description' => 'Completed a lesson before 7 AM',
            'icon' => 'badge-early-bird.png',
            'earned_date' => '22/01/2025'
        ]
    ]
];

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $user['name']);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Calculate total progress
$total_progress = 0;
$course_count = count($user['enrolled_courses']);
if ($course_count > 0) {
    foreach ($user['enrolled_courses'] as $course) {
        $total_progress += $course['progress'];
    }
    $total_progress = round($total_progress / $course_count);
}

// Get current date for greeting
$current_hour = date('H');
if ($current_hour < 12) {
    $greeting = "Good morning";
} elseif ($current_hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Dashboard</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="dashboard.css">
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
                    <li class="active"><a href="dashboard.php"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li><a href="my-courses.php"><i class="fas fa-book"></i>My Courses</a></li>
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
                    <h2>Dashboard</h2>
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
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <div class="welcome-message">
                        <h3><?php echo $greeting; ?>, <?php echo explode(' ', $user['name'])[0]; ?>!</h3>
                        <p>Welcome back to your learning dashboard. Here's your progress so far.</p>
                    </div>
                    <div class="quick-actions">
                        <a href="my-courses.php" class="quick-action-btn">
                            <i class="fas fa-book"></i>
                            <span>My Courses</span>
                        </a>
                        <a href="browse-courses.php" class="quick-action-btn">
                            <i class="fas fa-search"></i>
                            <span>Browse Courses</span>
                        </a>
                        <a href="settings.php" class="quick-action-btn">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </section>
                
                <!-- Stats Overview -->
                <section class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Enrolled Courses</h4>
                            <p class="stat-number"><?php echo $user['total_courses']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Completed</h4>
                            <p class="stat-number"><?php echo $user['completed_courses']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Learning Hours</h4>
                            <p class="stat-number"><?php echo $user['total_hours']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Certificates</h4>
                            <p class="stat-number"><?php echo $user['certificates']; ?></p>
                        </div>
                    </div>
                </section>
                
                <!-- Main Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Continue Learning Section -->
                    <section class="dashboard-card continue-learning">
                        <div class="card-header">
                            <h3>Continue Learning</h3>
                            <a href="my-courses.php" class="view-all-link">View All</a>
                        </div>
                        
                        <div class="continue-courses">
                            <?php foreach (array_slice($user['enrolled_courses'], 0, 2) as $course): ?>
                                <div class="continue-course-card">
                                    <div class="course-image">
                                        <img src="placeholder-course.png" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    </div>
                                    <div class="course-info">
                                        <h4 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                        <div class="progress-container">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo $course['progress']; ?>% completed</span>
                                        </div>
                                        <p class="next-lesson">
                                            <span>Next:</span> <?php echo htmlspecialchars($course['next_lesson']); ?>
                                        </p>
                                        <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Continue</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <!-- Learning Activity Section -->
                    <section class="dashboard-card learning-activity">
                        <div class="card-header">
                            <h3>Learning Activity</h3>
                        </div>
                        
                        <div class="activity-chart">
                            <h4>Weekly Learning Hours</h4>
                            <div class="chart-container">
                                <?php foreach ($user['learning_stats']['weekly_hours'] as $day => $hours): ?>
                                    <?php 
                                    $height = $hours * 20; // Scale the height
                                    $max_height = 100; // Maximum height in pixels
                                    $height = min($height, $max_height);
                                    ?>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: <?php echo $height; ?>px;"></div>
                                        <span class="bar-label"><?php echo ucfirst($day); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="skills-progress">
                            <h4>Skills Progress</h4>
                            <?php foreach ($user['learning_stats']['skills'] as $skill): ?>
                                <div class="skill-item">
                                    <div class="skill-info">
                                        <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                                        <span class="skill-level"><?php echo $skill['level']; ?>%</span>
                                    </div>
                                    <div class="skill-bar">
                                        <div class="skill-fill" style="width: <?php echo $skill['level']; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <!-- Recent Activity Section -->
                    <section class="dashboard-card recent-activity">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                        </div>
                        
                        <div class="activity-list">
                            <?php foreach ($user['recent_activities'] as $activity): ?>
                                <div class="activity-item">
                                    <?php if ($activity['type'] === 'lesson_completed'): ?>
                                        <div class="activity-icon lesson-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Completed lesson <strong><?php echo htmlspecialchars($activity['lesson']); ?></strong> in <a href="course.php?id=<?php echo $activity['course_id']; ?>"><?php echo htmlspecialchars($activity['course_title']); ?></a></p>
                                            <span class="activity-time"><?php echo htmlspecialchars($activity['date']); ?> at <?php echo htmlspecialchars($activity['time']); ?></span>
                                        </div>
                                    <?php elseif ($activity['type'] === 'quiz_completed'): ?>
                                        <div class="activity-icon quiz-icon">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Scored <strong><?php echo $activity['score']; ?>%</strong> on quiz in <a href="course.php?id=<?php echo $activity['course_id']; ?>"><?php echo htmlspecialchars($activity['course_title']); ?></a></p>
                                            <span class="activity-time"><?php echo htmlspecialchars($activity['date']); ?> at <?php echo htmlspecialchars($activity['time']); ?></span>
                                        </div>
                                    <?php elseif ($activity['type'] === 'course_started'): ?>
                                        <div class="activity-icon start-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Started course <a href="course.php?id=<?php echo $activity['course_id']; ?>"><?php echo htmlspecialchars($activity['course_title']); ?></a></p>
                                            <span class="activity-time"><?php echo htmlspecialchars($activity['date']); ?> at <?php echo htmlspecialchars($activity['time']); ?></span>
                                        </div>
                                    <?php elseif ($activity['type'] === 'certificate_earned'): ?>
                                        <div class="activity-icon certificate-icon">
                                            <i class="fas fa-certificate"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Earned certificate for <a href="course.php?id=<?php echo $activity['course_id']; ?>"><?php echo htmlspecialchars($activity['course_title']); ?></a></p>
                                            <span class="activity-time"><?php echo htmlspecialchars($activity['date']); ?> at <?php echo htmlspecialchars($activity['time']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <!-- Upcoming Events Section -->
                    <section class="dashboard-card upcoming-events">
                        <div class="card-header">
                            <h3>Upcoming Events</h3>
                            <a href="quiz.php" class="view-all-link">View Quizzes</a>
                        </div>
                        
                        <div class="events-list">
                            <?php foreach ($user['upcoming_events'] as $event): ?>
                                <div class="event-item">
                                    <?php if ($event['type'] === 'assignment'): ?>
                                        <div class="event-icon assignment-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                    <?php elseif ($event['type'] === 'live_session'): ?>
                                        <div class="event-icon live-icon">
                                            <i class="fas fa-video"></i>
                                        </div>
                                    <?php elseif ($event['type'] === 'exam'): ?>
                                        <div class="event-icon exam-icon">
                                            <i class="fas fa-pen"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="event-content">
                                        <h4 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p class="event-course"><?php echo htmlspecialchars($event['course_title']); ?></p>
                                        <p class="event-date">
                                            <?php if ($event['type'] === 'assignment'): ?>
                                                Due: <?php echo htmlspecialchars($event['due_date']); ?>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($event['date']); ?> at <?php echo htmlspecialchars($event['time']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <!-- Achievements Section -->
                    <section class="dashboard-card achievements">
                        <div class="card-header">
                            <h3>Your Achievements</h3>
                        </div>
                        
                        <div class="badges-container">
                            <?php foreach ($user['badges'] as $badge): ?>
                                <div class="badge-item">
                                    <div class="badge-icon">
                                        <i class="fas fa-award"></i>
                                    </div>
                                    <div class="badge-content">
                                        <h4 class="badge-title"><?php echo htmlspecialchars($badge['title']); ?></h4>
                                        <p class="badge-desc"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        <span class="badge-date">Earned on <?php echo htmlspecialchars($badge['earned_date']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <!-- Recommended Courses Section -->
                    <section class="dashboard-card recommended-courses">
                        <div class="card-header">
                            <h3>Recommended For You</h3>
                            <a href="browse-courses.php" class="view-all-link">Browse All</a>
                        </div>
                        
                        <div class="recommended-list">
                            <?php foreach ($user['recommended_courses'] as $course): ?>
                                <div class="recommended-course-card">
                                    <div class="course-image">
                                        <img src="placeholder-course.png" alt="<?php echo htmlspecialchars($course['title']); ?>">
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
                                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline">View Course</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
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
        });
    </script>
</body>
</html>
