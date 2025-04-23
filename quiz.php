<?php
// quiz.php - User Quiz Page

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
    'courses_with_quizzes' => [
        [
            'id' => 1,
            'title' => 'Introduction to Web Development',
            'category' => 'HTML',
            'progress' => 75,
            'image' => 'course-web-dev.jpg',
            'quizzes' => [
                [
                    'id' => 101,
                    'title' => 'HTML Basics Quiz',
                    'questions' => 10,
                    'time_limit' => 15, // minutes
                    'completed' => true,
                    'score' => 85,
                    'passing_score' => 70
                ],
                [
                    'id' => 102,
                    'title' => 'CSS Fundamentals Quiz',
                    'questions' => 12,
                    'time_limit' => 20, // minutes
                    'completed' => false,
                    'score' => null,
                    'passing_score' => 70
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'Advanced Digital Marketing',
            'category' => 'Marketing',
            'progress' => 45,
            'image' => 'course-marketing.jpg',
            'quizzes' => [
                [
                    'id' => 201,
                    'title' => 'Social Media Marketing Quiz',
                    'questions' => 15,
                    'time_limit' => 25, // minutes
                    'completed' => true,
                    'score' => 92,
                    'passing_score' => 75
                ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'Photography for Beginners',
            'category' => 'Photography',
            'progress' => 90,
            'image' => 'course-photography.jpg',
            'quizzes' => [
                [
                    'id' => 301,
                    'title' => 'Camera Basics Quiz',
                    'questions' => 8,
                    'time_limit' => 10, // minutes
                    'completed' => true,
                    'score' => 75,
                    'passing_score' => 60
                ],
                [
                    'id' => 302,
                    'title' => 'Composition Techniques Quiz',
                    'questions' => 10,
                    'time_limit' => 15, // minutes
                    'completed' => true,
                    'score' => 90,
                    'passing_score' => 70
                ],
                [
                    'id' => 303,
                    'title' => 'Lighting Fundamentals Quiz',
                    'questions' => 12,
                    'time_limit' => 20, // minutes
                    'completed' => false,
                    'score' => null,
                    'passing_score' => 70
                ]
            ]
        ],
        [
            'id' => 4,
            'title' => 'CSS Fundamentals',
            'category' => 'CSS',
            'progress' => 60,
            'image' => 'course-css.jpg',
            'quizzes' => [
                [
                    'id' => 401,
                    'title' => 'CSS Selectors Quiz',
                    'questions' => 10,
                    'time_limit' => 15, // minutes
                    'completed' => true,
                    'score' => 80,
                    'passing_score' => 70
                ],
                [
                    'id' => 402,
                    'title' => 'CSS Layout Quiz',
                    'questions' => 12,
                    'time_limit' => 20, // minutes
                    'completed' => false,
                    'score' => null,
                    'passing_score' => 70
                ]
            ]
        ],
        [
            'id' => 5,
            'title' => 'PHP for Beginners',
            'category' => 'PHP',
            'progress' => 30,
            'image' => 'course-php.jpg',
            'quizzes' => [
                [
                    'id' => 501,
                    'title' => 'PHP Syntax Quiz',
                    'questions' => 10,
                    'time_limit' => 15, // minutes
                    'completed' => false,
                    'score' => null,
                    'passing_score' => 70
                ]
            ]
        ]
    ]
];

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $user['name']);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Get active course from URL parameter
$activeCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

// Calculate quiz statistics
$total_quizzes = 0;
$completed_quizzes = 0;
$average_score = 0;
$total_score = 0;
$score_count = 0;

foreach ($user['courses_with_quizzes'] as $course) {
    foreach ($course['quizzes'] as $quiz) {
        $total_quizzes++;
        if ($quiz['completed']) {
            $completed_quizzes++;
            $total_score += $quiz['score'];
            $score_count++;
        }
    }
}

$average_score = $score_count > 0 ? round($total_score / $score_count) : 0;
$completion_rate = $total_quizzes > 0 ? round(($completed_quizzes / $total_quizzes) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Quizzes</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="quiz.css">
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
                    <li><a href="my-courses.php"><i class="fas fa-book"></i>My Courses</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                    <li class="active"><a href="quiz.php"><i class="fas fa-question-circle"></i>Quiz</a></li>
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
                    <h2>Quizzes</h2>
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
                <!-- Quiz Overview Section -->
                <section class="quiz-overview">
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Total Quizzes</h3>
                            <p class="overview-number"><?php echo $total_quizzes; ?></p>
                        </div>
                    </div>
                    
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Completed</h3>
                            <p class="overview-number"><?php echo $completed_quizzes; ?></p>
                        </div>
                    </div>
                    
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Completion Rate</h3>
                            <p class="overview-number"><?php echo $completion_rate; ?>%</p>
                        </div>
                    </div>
                    
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Average Score</h3>
                            <p class="overview-number"><?php echo $average_score; ?>%</p>
                        </div>
                    </div>
                </section>
                
                <!-- Course Quizzes Section -->
                <section class="course-quizzes">
                    <div class="course-list">
                        <h3 class="section-title">My Courses</h3>
                        <ul class="course-nav">
                            <?php foreach ($user['courses_with_quizzes'] as $course): ?>
                                <li class="<?php echo ($activeCourseId === $course['id']) ? 'active' : ''; ?>">
                                    <a href="quiz.php?course_id=<?php echo $course['id']; ?>">
                                        <span class="course-name"><?php echo htmlspecialchars($course['title']); ?></span>
                                        <span class="quiz-count"><?php echo count($course['quizzes']); ?> quizzes</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="quiz-content">
                        <?php if ($activeCourseId): ?>
                            <?php 
                            // Find the active course
                            $activeCourse = null;
                            foreach ($user['courses_with_quizzes'] as $course) {
                                if ($course['id'] === $activeCourseId) {
                                    $activeCourse = $course;
                                    break;
                                }
                            }
                            ?>
                            
                            <?php if ($activeCourse): ?>
                                <div class="course-header">
                                    <h3><?php echo htmlspecialchars($activeCourse['title']); ?> Quizzes</h3>
                                    <div class="course-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $activeCourse['progress']; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo $activeCourse['progress']; ?>% course completed</span>
                                    </div>
                                </div>
                                
                                <div class="quiz-list">
                                    <?php foreach ($activeCourse['quizzes'] as $quiz): ?>
                                        <div class="quiz-card <?php echo $quiz['completed'] ? 'completed' : ''; ?>">
                                            <div class="quiz-status">
                                                <?php if ($quiz['completed']): ?>
                                                    <div class="status-badge completed">
                                                        <i class="fas fa-check-circle"></i> Completed
                                                    </div>
                                                <?php else: ?>
                                                    <div class="status-badge pending">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h4 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                            
                                            <div class="quiz-details">
                                                <div class="quiz-detail">
                                                    <i class="fas fa-question-circle"></i>
                                                    <span><?php echo $quiz['questions']; ?> questions</span>
                                                </div>
                                                <div class="quiz-detail">
                                                    <i class="fas fa-clock"></i>
                                                    <span><?php echo $quiz['time_limit']; ?> minutes</span>
                                                </div>
                                                <div class="quiz-detail">
                                                    <i class="fas fa-award"></i>
                                                    <span>Pass: <?php echo $quiz['passing_score']; ?>%</span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($quiz['completed']): ?>
                                                <div class="quiz-score">
                                                    <div class="score-circle <?php echo ($quiz['score'] >= $quiz['passing_score']) ? 'passed' : 'failed'; ?>">
                                                        <span class="score-number"><?php echo $quiz['score']; ?>%</span>
                                                    </div>
                                                    <div class="score-label">
                                                        <?php if ($quiz['score'] >= $quiz['passing_score']): ?>
                                                            <span class="passed">Passed</span>
                                                        <?php else: ?>
                                                            <span class="failed">Failed</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="quiz-actions">
                                                    <a href="quiz-review.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline">Review Answers</a>
                                                    <a href="quiz-retake.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Retake Quiz</a>
                                                </div>
                                            <?php else: ?>
                                                <div class="quiz-actions">
                                                    <a href="quiz-start.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Start Quiz</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-course-selected">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-list"></i>
                                        <h3>Select a Course</h3>
                                        <p>Please select a course from the list to view available quizzes.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-course-selected">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <h3>Select a Course</h3>
                                    <p>Please select a course from the list to view available quizzes.</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
        });
    </script>
</body>
</html>
