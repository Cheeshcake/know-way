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
    <style>
        :root {
            /* Modern color palette */
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #4cc9f0;
            --accent: #7209b7;
            --accent-light: #9d4edd;
            --white: #ffffff;
            --off-white: #f8f9fa;
            --light-gray: #e9ecef;
            --mid-gray: #dee2e6;
            --gray: #6c757d;
            --dark-gray: #343a40;
            --black: #212529;
            --green: #198754;
            --yellow: #ffc107;
            --red: #dc3545;
            
            /* Shadows */
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.1);
            
            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--off-white);
            color: var(--dark-gray);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--white);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: var(--transition-normal);
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--white);
            text-decoration: none;
            max-width: 80px;
        }
        
        .logo-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 100%;
        }
        
        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 24px 0;
        }
        
        .nav-section {
            margin-bottom: 24px;
        }
        
        .nav-section-title {
            padding: 0 24px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }
        
        .nav-links {
            list-style: none;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition-fast);
            position: relative;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--white);
            border-radius: 0 4px 4px 0;
        }
        
        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            opacity: 0.8;
        }
        
        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        
        
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition-fast);
            font-weight: 500;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 32px;
            transition: var(--transition-normal);
        }
        
        .page-header {
            margin-bottom: 32px;
        }
        
        .greeting {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--black);
        }
        
        .subheading {
            color: var(--gray);
            font-size: 1rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        .card {
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition-normal);
        }
        
        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }
        
        .card-header {
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--black);
        }
        
        .card-link {
            color: var(--primary);
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition-fast);
        }
        
        .card-link:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Progress Section */
        .progress-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: var(--white);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-normal);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent) 100%);
        }
        
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary-light) 100%);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .current-course {
            background-color: var(--off-white);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .course-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--black);
        }
        
        .course-badge {
            background-color: var(--primary-light);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .progress-container {
            margin-bottom: 12px;
        }
        
        .progress-bar-container {
            height: 8px;
            background-color: var(--mid-gray);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--accent));
            width: 75%; /* Example progress */
            border-radius: 4px;
        }
        
        .progress-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .next-lesson {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--light-gray);
        }
        
        .next-lesson-label {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 8px;
        }
        
        .next-lesson-title {
            font-weight: 500;
            color: var(--primary);
        }
        
        .continue-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            margin-top: 16px;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .continue-btn:hover {
            background-color: var(--accent);
            transform: translateY(-2px);
        }
        
        /* Courses Grid */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .course-card {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-normal);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .course-image {
            height: 160px;
            overflow: hidden;
            position: relative;
        }
        
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .course-card:hover .course-image img {
            transform: scale(1.05);
        }
        
        .course-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .course-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--black);
            line-height: 1.4;
        }
        
        .course-info {
            margin-top: auto;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .enroll-btn {
            background-color: var(--primary-light);
            color: var(--white);
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition-fast);
        }
        
        .enroll-btn:hover {
            background-color: var(--primary);
        }
        
        
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .courses-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background-color: var(--white);
                border-radius: 8px;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 99;
                box-shadow: var(--shadow-md);
                border: none;
                cursor: pointer;
            }
            
            .mobile-menu-btn span {
                display: block;
                width: 20px;
                height: 2px;
                background-color: var(--primary);
                position: relative;
                transition: var(--transition-fast);
            }
            
            .mobile-menu-btn span:before,
            .mobile-menu-btn span:after {
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                background-color: var(--primary);
                transition: var(--transition-fast);
            }
            
            .mobile-menu-btn span:before {
                transform: translateY(-6px);
            }
            
            .mobile-menu-btn span:after {
                transform: translateY(6px);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 24px 16px;
            }
            
            .progress-stats {
                grid-template-columns: 1fr;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .greeting {
                font-size: 1.5rem;
            }
        }
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition-normal);
        }
        
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <span></span>
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
                        <a href="#" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
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
                        <a href="#" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                            </svg>
                            Bookmarks
                        </a>
                    </li>
                   <li>
                    <a href="#" class="nav-link">
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
                        <a href="#" class="nav-link">
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
                <div class="user-avatar">
                    <?php echo substr($username, 0, 1); ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $username; ?></div>
                    <div class="user-role">Learner</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
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
                            <div class="stat-value"><?php echo $user_progress['courses_completed']; ?></div>
                            <div class="stat-label">Courses Completed</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">75%</div>
                            <div class="stat-label">Overall Progress</div>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            
            <!-- Recommended Courses Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recommended Courses</h2>
                    <a href="#" class="card-link">Browse All</a>
                </div>
                <div class="card-body">
                    <div class="courses-grid">
                        <?php
                        if ($courses_result && $courses_result->num_rows > 0) {
                            while($course = $courses_result->fetch_assoc()) {
                                echo '
                                <div class="course-card">
                                    <div class="course-image">
                                        <img src="uploads/' . ($course['image'] ?? 'assets/images/course-placeholder.jpg') . '" alt="' . $course['title'] . '">
                                    </div>
                                    <div class="course-content">
                                        <h3 class="course-title">' . $course['title'] . '</h3>
                                        <div class="course-info">
                                            <span>' . ($course['quizzes_count'] ?? '0') . ' Quizzes</span>
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