<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: index.html');
    exit;
}

// Check if user is an admin (role-based access control)
if ($_SESSION['role'] !== 'admin') {
    // Redirect to learner dashboard if not admin
    header('Location: dashboard.php');
    exit;
}

// Store session username in a different variable to avoid conflicts
$user_session_name = $_SESSION['username'];
$initials = '';
$name_parts = explode(' ', $user_session_name);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Include database - note this defines its own $username for DB connection
include '../config/db.php';

// Fetch user details for avatar (if needed)
$user_id = $_SESSION['user_id'];
$user_query = "SELECT avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user_avatar = null;
if ($result_user->num_rows === 1) {
    $user_data = $result_user->fetch_assoc();
    $user_avatar = $user_data['avatar'];
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Query to get pending courses
$sql = "SELECT c.*, u.username as creator_name 
        FROM courses c 
        JOIN users u ON c.creator_id = u.id
        WHERE c.status = 'pending' AND c.title LIKE ?";

if ($filter) {
    $sql .= " ORDER BY c.created_at " . ($filter === 'newest' ? "DESC" : "ASC");
} else {
    $sql .= " ORDER BY c.created_at DESC";
}

$sql .= " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("sii", $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Count total pending courses
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM courses WHERE status = 'pending' AND title LIKE ?");
$count_stmt->bind_param("s", $search_param);
$count_stmt->execute();
$total_pending = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_pending / $limit);

$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - Pending Courses</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        /* Enhanced styling for the content area */
        .content-body {
            padding: 24px;
            background-color: #f9fafb;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Improved alert styling */
        .alert {
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }
        
        .alert-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .alert-close:hover {
            opacity: 1;
        }
        
        /* Enhanced toolbar */
        .toolbar {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .search-input-wrapper {
            flex-grow: 1;
            position: relative;
        }
        
        .search-input-wrapper input {
            width: 100%;
            padding: 12px 16px;
            padding-right: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .search-input-wrapper input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .search-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>') no-repeat center;
            width: 20px;
            height: 20px;
            border: none;
            cursor: pointer;
        }
        
        .filter-select {
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: white;
            font-size: 0.95rem;
            min-width: 140px;
            cursor: pointer;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        /* Enhanced course cards */
        .courses-container {
            margin-top: 20px;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        
        .course-card {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .course-img {
            height: 180px;
            overflow: hidden;
        }
        
        .course-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .course-card:hover .course-img img {
            transform: scale(1.05);
        }
        
        .course-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .course-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .course-desc {
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 12px;
            flex-grow: 1;
        }
        
        .course-creator {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }
        
        .course-creator:before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>');
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .course-date {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }
        
        .course-date:before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* Enhanced action buttons */
        .course-actions {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        
        .course-actions form {
            display: contents;
        }
        
        .view-btn, .approve-btn, .reject-btn {
            font-weight: 500;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .view-btn {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
        }
        
        .view-btn:hover {
            background-color: #4338ca;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }
        
        .view-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>');
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .approve-btn {
            background-color: #10b981;
            color: white;
            box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
        }
        
        .approve-btn:hover {
            background-color: #059669;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }
        
        .approve-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .reject-btn {
            background-color: #ef4444;
            color: white;
            box-shadow: 0 1px 2px rgba(239, 68, 68, 0.2);
        }
        
        .reject-btn:hover {
            background-color: #dc2626;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }
        
        .reject-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>');
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .rejection-reason {
            margin-top: 10px;
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
            resize: vertical;
            min-height: 80px;
            display: block;
            grid-column: span 3;
            width: 100%;
            box-sizing: border-box;
        }
        
        .rejection-reason:focus {
            outline: none;
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        .rejection-reason::placeholder {
            color: #9ca3af;
        }
        
        /* Enhanced empty state */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background-color: #ffffff;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .empty-state:before {
            content: '';
            display: block;
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="%23d1d5db" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .empty-state h3 {
            color: #111827;
            font-size: 1.25rem;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6b7280;
            font-size: 1rem;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Enhanced pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            gap: 8px;
        }
        
        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            color: #4b5563;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .pagination-btn:hover {
            background-color: #f9fafb;
            color: #111827;
            border-color: #d1d5db;
        }
        
        .pagination-numbers {
            display: flex;
            gap: 6px;
        }
        
        .pagination-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            color: #4b5563;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .pagination-number:hover {
            background-color: #f9fafb;
            color: #111827;
            border-color: #d1d5db;
        }
        
        .pagination-number.active {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-wrapper {
                width: 100%;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .course-actions {
                grid-template-columns: 1fr;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .courses-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation - Keeping this unchanged as requested -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin.php"><span class="nav-icon dashboard-icon"></span>Dashboard</a></li>
                    <li class="active"><a href="pending-courses.php"><span class="nav-icon courses-icon"></span>Pending Courses</a></li>
                    <li><a href="users.php"><span class="nav-icon users-icon"></span>Users</a></li>
                    <li><a href="settings.php"><span class="nav-icon settings-icon"></span>Settings</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../controller/logout.php" class="logout-btn">Sign Out</a>
            </div>
        </aside>
        
        <!-- Enhanced Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h2>Pending Course Approval</h2>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-weight: 600; flex-shrink: 0; text-align: center;">
                            <?php if (isset($user_avatar) && $user_avatar): ?>
                                <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; display: block; margin: 0; padding: 0;">
                            <?php else: ?>
                                <span style="font-size: 16px;"><?php echo htmlspecialchars($initials); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="admin-name"><?= htmlspecialchars($user_session_name) ?></span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" id="successAlert">
                    <span><?= htmlspecialchars($success_message) ?></span>
                    <button class="alert-close" onclick="closeAlert('successAlert')">&times;</button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" id="errorAlert">
                    <span><?= htmlspecialchars($error_message) ?></span>
                    <button class="alert-close" onclick="closeAlert('errorAlert')">&times;</button>
                </div>
                <?php endif; ?>
                
                <div class="toolbar">
                    <div class="search-filter">
                        <form method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <input type="text" name="search" placeholder="Search pending courses..." value="<?= htmlspecialchars($search) ?>">
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
                </div>
                
                <div class="courses-container">
                    <h3 class="section-title">Courses Awaiting Approval (<?= $total_pending ?>)</h3>
                    
                    <?php if ($result->num_rows > 0): ?>
                    <div class="courses-grid">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="course-card">
                                <div class="course-img">
                                    <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" onerror="this.src='placeholder-course.png'">
                                </div>
                                <div class="course-content">
                                    <h4 class="course-title"><?= htmlspecialchars($row['title']) ?></h4>
                                    <p class="course-desc"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</p>
                                    <span class="course-creator">Created by: <?= htmlspecialchars($row['creator_name']) ?></span>
                                    <span class="course-date">Submitted: <?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                                    
                                    <div class="course-actions">
                                        <button class="view-btn" onclick="window.location.href='course-details.php?id=<?= $row['id'] ?>'">Preview</button>
                                        
                                        <form method="POST" action="../controller/approve-course.php" onsubmit="return confirm('Are you sure you want to approve this course?');">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="approve-btn">Approve</button>
                                        </form>
                                        
                                        <form method="POST" action="../controller/reject-course.php" onsubmit="return confirm('Are you sure you want to reject this course?');">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="reject-btn">Reject</button>
                                            <textarea name="reason" class="rejection-reason" placeholder="Rejection reason (optional)"></textarea>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <h3>No Pending Courses</h3>
                        <p>There are no courses waiting for approval at this time.</p>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.querySelector('.admin-container');
            
            menuToggle.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
        });
        
        function closeAlert(alertId) {
            document.getElementById(alertId).style.display = 'none';
        }
    </script>
</body>
</html>