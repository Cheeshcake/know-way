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

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$status_condition = '';
if ($status_filter !== 'all') {
    $status_condition = "AND status = '$status_filter'";
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

// Sorting options
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_by = "created_at DESC"; // Default sorting

if ($sort === 'oldest') {
    $order_by = "created_at ASC";
} elseif ($sort === 'title_asc') {
    $order_by = "title ASC";
} elseif ($sort === 'title_desc') {
    $order_by = "title DESC";
}

// Get total number of user's courses
$count_sql = "SELECT COUNT(*) as total FROM courses WHERE creator_id = ? $status_condition $search_condition";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_courses = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get courses created by the user
$courses_sql = "SELECT c.*, COUNT(cq.id) as quizzes_count 
                FROM courses c 
                LEFT JOIN course_quizzes cq ON c.id = cq.course_id 
                WHERE c.creator_id = ? $status_condition $search_condition 
                GROUP BY c.id 
                ORDER BY $order_by";
$stmt = $conn->prepare($courses_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$stmt->close();

// Get counts by status for the sidebar
$status_counts = [];
$status_types = ['draft', 'pending', 'published'];

foreach ($status_types as $type) {
    $count_sql = "SELECT COUNT(*) as count FROM courses WHERE creator_id = ? AND status = ?";
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param("is", $user_id, $type);
    $stmt->execute();
    $status_counts[$type] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - My Courses</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .search-box {
            flex: 1;
            max-width: 400px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 16px;
            padding-right: 40px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .search-box button {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .search-box button:hover {
            opacity: 1;
        }
        
        .sorting-options {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sort-label {
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .sort-select {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            font-size: 0.95rem;
            color: var(--dark-gray);
            background-color: var(--white);
            cursor: pointer;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .course-card {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .course-image {
            height: 160px;
            overflow: hidden;
        }
        
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .course-card:hover .course-image img {
            transform: scale(1.05);
        }
        
        .course-content {
            padding: 16px;
        }
        
        .course-title {
            margin: 0 0 8px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .course-description {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        
        .course-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .course-actions {
            display: flex;
            gap: 8px;
        }
        
        .status-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            z-index: 1;
        }
        
        .status-draft {
            background-color: #e9ecef;
            color: #495057;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-published {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .create-course-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 24px;
            border: none;
            cursor: pointer;
            gap: 8px;
        }
        
        .create-course-btn:hover {
            background-color: var(--primary);
            opacity: 0.9;
        }
        
        .create-course-btn svg {
            width: 18px;
            height: 18px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .view-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .edit-btn {
            background-color: #e9ecef;
            color: #495057;
        }

        .no-courses {
            text-align: center;
            padding: 48px 0;
            color: var(--gray);
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .status-filter {
            display: flex;
            margin-bottom: 24px;
            border-radius: 8px;
            overflow: hidden;
            background-color: var(--white);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .status-filter a {
            padding: 12px 24px;
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            transition: all 0.2s;
            flex: 1;
            text-align: center;
            border-bottom: 2px solid transparent;
        }
        
        .status-filter a.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .status-filter a:hover:not(.active) {
            background-color: var(--very-light-gray);
        }
        
        .status-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            border-radius: 50%;
            font-size: 0.75rem;
            margin-left: 4px;
        }
        
        .status-filter a.active .status-count {
            background-color: var(--primary);
            color: white;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            color: var(--light-gray);
            margin-bottom: 24px;
        }
        
        .empty-state h3 {
            margin: 0 0 8px;
            color: var(--dark);
            font-size: 1.25rem;
        }
        
        .empty-state p {
            color: var(--gray);
            margin-bottom: 24px;
            max-width: 400px;
        }
    </style>
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
                        <a href="dashboard.php" class="nav-link">
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
                        <a href="my-courses.php" class="nav-link active">
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
            <h1 class="greeting">My Courses</h1>
            <p class="subheading">Manage your created courses</p>
        </div>
        
        <!-- Create Course Button -->
        <a href="create-course.php" class="create-course-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Create New Course
        </a>
        
        <!-- Status Filter -->
        <div class="status-filter">
            <a href="?status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>" class="<?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                All <span class="status-count"><?php echo $total_courses; ?></span>
            </a>
            <a href="?status=draft<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>" class="<?php echo $status_filter === 'draft' ? 'active' : ''; ?>">
                Drafts <span class="status-count"><?php echo $status_counts['draft']; ?></span>
            </a>
            <a href="?status=pending<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>" class="<?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                Pending <span class="status-count"><?php echo $status_counts['pending']; ?></span>
            </a>
            <a href="?status=published<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>" class="<?php echo $status_filter === 'published' ? 'active' : ''; ?>">
                Published <span class="status-count"><?php echo $status_counts['published']; ?></span>
            </a>
        </div>
        
        <!-- Filters and Sorting -->
        <form class="filter-container" method="GET" action="my-courses.php">
            <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </div>
            
            <div class="sorting-options">
                <span class="sort-label">Sort by:</span>
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                    <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Title (A-Z)</option>
                    <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Title (Z-A)</option>
                </select>
            </div>
        </form>
        
        <?php if ($courses_result && $courses_result->num_rows > 0): ?>
            <!-- Courses Grid -->
            <div class="courses-grid">
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <div class="course-card">
                        <div class="status-tag status-<?php echo $course['status']; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </div>
                        <div class="course-image">
                            <img src="uploads/<?php echo ($course['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.src='assets/placeholder-course.png'">
                        </div>
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-description">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 80)) . (strlen($course['description']) > 80 ? '...' : ''); ?>
                            </p>
                            <div class="course-info">
                                <span><?php echo $course['quizzes_count']; ?> Quizzes</span>
                                <div class="course-actions">
                                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="action-btn view-btn">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    <line x1="12" y1="6" x2="20" y2="6"></line>
                    <line x1="12" y1="12" x2="20" y2="12"></line>
                    <line x1="12" y1="18" x2="20" y2="18"></line>
                </svg>
                <h3>No courses found</h3>
                <?php if (!empty($search)): ?>
                    <p>No courses match your search criteria. Try a different search term.</p>
                <?php elseif ($status_filter !== 'all'): ?>
                    <p>You don't have any <?php echo $status_filter; ?> courses. Switch to a different filter or create a new course.</p>
                <?php else: ?>
                    <p>You haven't created any courses yet. Click the button below to get started!</p>
                <?php endif; ?>
                <a href="create-course.php" class="create-course-btn">Create Your First Course</a>
            </div>
        <?php endif; ?>
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
