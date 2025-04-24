<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['role']) || !$_SESSION['role']) {
    // Redirect non-admin users
    header('Location: dashboard.php');
    exit;
}

include 'db.php';

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    header('Location: users.php?error=Invalid user ID');
    exit;
}

// In a real application, fetch user data from database
// For this example, we'll use sample data
$userData = [
    'id' => $userId,
    'name' => 'Sophie Martin',
    'email' => 'sophie.martin@example.com',
    'role' => 'student',
    'status' => 'active',
    'enrolledCourses' => 3,
    'lastActive' => '15/04/2023',
    'joinDate' => '10/01/2023',
    'phone' => '+33 6 12 34 56 78',
    'bio' => 'Student passionate about web development and design.',
    'courses' => [
        [
            'id' => 1,
            'title' => 'Introduction to Web Development',
            'progress' => 75,
            'lastActivity' => '18/04/2023'
        ],
        [
            'id' => 2,
            'title' => 'Advanced Digital Marketing',
            'progress' => 45,
            'lastActivity' => '10/03/2023'
        ],
        [
            'id' => 3,
            'title' => 'Photography for Beginners',
            'progress' => 90,
            'lastActivity' => '04/10/2023'
        ]
    ],
    'activities' => [
        [
            'type' => 'course_started',
            'details' => 'Started course: Introduction to Web Development',
            'date' => '10/01/2023'
        ],
        [
            'type' => 'quiz_completed',
            'details' => 'Completed quiz in Advanced Digital Marketing with score 85%',
            'date' => '15/02/2023'
        ],
        [
            'type' => 'course_completed',
            'details' => 'Completed course: Photography for Beginners',
            'date' => '20/03/2023'
        ]
    ]
];

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $userData['name']);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

$admin_initials = 'A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - User Details</title>
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
                    <li><a href="admin-courses.php"><i class="fas fa-book"></i>Courses</a></li>
                    <li class="active"><a href="users.php"><i class="fas fa-users"></i>Users</a></li>
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
                    <h2>User Details</h2>
                </div>
                
                <div class="header-right">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo htmlspecialchars($admin_initials); ?></div>
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <div class="user-detail-header">
                    <a href="users.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Users</a>
                    
                    <div class="user-detail-actions">
                        <a href="edit-user.php?id=<?php echo $userData['id']; ?>" class="btn btn-outline"><i class="fas fa-edit"></i> Edit User</a>
                        <button class="btn btn-danger" onclick="confirmDeleteUser(<?php echo $userData['id']; ?>)"><i class="fas fa-trash"></i> Delete User</button>
                    </div>
                </div>
                
                <div class="user-detail-container">
                    <div class="user-profile-card">
                        <div class="user-profile-header">
                            <div class="user-avatar large">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <h3 class="user-name"><?php echo htmlspecialchars($userData['name']); ?></h3>
                            <p class="user-email"><?php echo htmlspecialchars($userData['email']); ?></p>
                            <span class="status-badge status-<?php echo $userData['status']; ?>">
                                <?php echo ucfirst($userData['status']); ?>
                            </span>
                        </div>
                        
                        <div class="user-profile-info">
                            <div class="info-item">
                                <span class="info-label">Role</span>
                                <span class="info-value"><?php echo ucfirst($userData['role']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Joined</span>
                                <span class="info-value"><?php echo $userData['joinDate']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Active</span>
                                <span class="info-value"><?php echo $userData['lastActive']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone</span>
                                <span class="info-value"><?php echo htmlspecialchars($userData['phone']); ?></span>
                            </div>
                        </div>
                        
                        <div class="user-profile-bio">
                            <h4>Biography</h4>
                            <p><?php echo htmlspecialchars($userData['bio']); ?></p>
                        </div>
                    </div>
                    
                    <div class="user-detail-content">
                        <div class="user-courses">
                            <h3>Enrolled Courses</h3>
                            
                            <?php if (count($userData['courses']) > 0): ?>
                                <div class="courses-list">
                                    <?php foreach ($userData['courses'] as $course): ?>
                                        <div class="course-item">
                                            <div class="course-info">
                                                <h4 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                                <p class="course-meta">Last activity: <?php echo $course['lastActivity']; ?></p>
                                            </div>
                                            
                                            <div class="course-progress">
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                                                </div>
                                                <span class="progress-text"><?php echo $course['progress']; ?>% completed</span>
                                            </div>
                                            
                                            <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline">View Course</a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-data">This user is not enrolled in any courses.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="user-activity">
                            <h3>Recent Activity</h3>
                            
                            <?php if (count($userData['activities']) > 0): ?>
                                <div class="activity-timeline">
                                    <?php foreach ($userData['activities'] as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon 
                                                <?php 
                                                    if ($activity['type'] === 'course_started') echo 'icon-start';
                                                    elseif ($activity['type'] === 'quiz_completed') echo 'icon-quiz';
                                                    elseif ($activity['type'] === 'course_completed') echo 'icon-complete';
                                                ?>
                                            ">
                                                <?php 
                                                    if ($activity['type'] === 'course_started') echo '<i class="fas fa-play"></i>';
                                                    elseif ($activity['type'] === 'quiz_completed') echo '<i class="fas fa-clipboard-check"></i>';
                                                    elseif ($activity['type'] === 'course_completed') echo '<i class="fas fa-certificate"></i>';
                                                ?>
                                            </div>
                                            <div class="activity-content">
                                                <p class="activity-details"><?php echo htmlspecialchars($activity['details']); ?></p>
                                                <span class="activity-date"><?php echo $activity['date']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-data">No recent activity for this user.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Delete User Form (Hidden) -->
    <form id="deleteUserForm" method="POST" action="delete-user.php" style="display: none;">
        <input type="hidden" name="id" id="deleteUserId">
    </form>
    
    <script>
        function confirmDeleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteUserForm').submit();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            // Toggle sidebar
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>
