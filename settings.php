<?php
// settings.php - User Settings Page (English version)

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
    'phone' => '+216 24 682 456',
    'bio' => 'Student passionate about online learning and personal development.',
    'notifications' => [
        'email_course_updates' => true,
        'email_new_messages' => true,
        'email_reminders' => false,
        'browser_notifications' => true,
        'sms_notifications' => false
    ],
    'security' => [
        'two_factor_auth' => false,
        'last_password_change' => '22/04/2025',
        'active_sessions' => 2
    ],
    'courses' => [
        [
            'id' => 1,
            'title' => 'Introduction to Web Development',
            'progress' => 75,
            'last_activity' => '18/04/2025'
        ],
        [
            'id' => 2,
            'title' => 'Advanced Digital Marketing',
            'progress' => 45,
            'last_activity' => '10/03/2025'
        ],
        [
            'id' => 3,
            'title' => 'Photography for Beginners',
            'progress' => 90,
            'last_activity' => '04/10/2025'
        ]
    ]
];

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_general') {
        header('Location: settings.php?tab=general&success=1');
        exit;
    } 
    elseif ($_POST['action'] === 'update_security') {
        header('Location: settings.php?tab=security&success=1');
        exit;
    }
    elseif ($_POST['action'] === 'update_notifications') {
        header('Location: settings.php?tab=notifications&success=1');
        exit;
    }
}

$activeTab = $_GET['tab'] ?? 'general';
$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $user['name']);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - User Settings</title>
    <link rel="stylesheet" href="settings.css">
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
                    <li><a href="quiz.php"><i class="fas fa-question-circle"></i>Quiz</a></li>
                    <li class="active"><a href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
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
                    <h2>Account Settings</h2>
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
                <?php if ($showSuccess): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Your settings have been successfully updated.
                    <button class="close-alert"><i class="fas fa-times"></i></button>
                </div>
                <?php endif; ?>
                
                <div class="settings-container">
                    <div class="settings-sidebar">
                        <div class="user-info-card">
                            <div class="user-avatar large">
                                <?php if ($user['avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                            </div>
                            <h3 class="user-name"><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        
                        <nav class="settings-nav">
                            <a href="?tab=general" class="settings-nav-item <?php echo $activeTab === 'general' ? 'active' : ''; ?>">
                                <i class="fas fa-user"></i>
                                General Information
                            </a>
                            <a href="?tab=security" class="settings-nav-item <?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                                <i class="fas fa-shield-alt"></i>
                                Security
                            </a>
                            <a href="?tab=notifications" class="settings-nav-item <?php echo $activeTab === 'notifications' ? 'active' : ''; ?>">
                                <i class="fas fa-bell"></i>
                                Notifications
                            </a>
                            <a href="my-courses.php" class="settings-nav-item">
                                <i class="fas fa-book"></i>
                                My Courses
                            </a>
                        </nav>
                    </div>
                    
                    <div class="settings-content">
                        <?php if ($activeTab === 'general'): ?>
                            <div class="settings-panel">
                                <h3 class="settings-panel-title">General Information</h3>
                                <p class="settings-panel-description">Update your personal information and profile.</p>
                                
                                <form method="POST" action="settings.php" class="settings-form" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_general">
                                    
                                    <div class="form-group">
                                        <label for="avatar">Profile Picture</label>
                                        <div class="avatar-upload">
                                            <div class="avatar-preview">
                                                <?php if ($user['avatar']): ?>
                                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <?php echo htmlspecialchars($initials); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="avatar-actions">
                                                <label for="avatar-input" class="btn btn-outline">
                                                    <i class="fas fa-upload"></i> Upload
                                                </label>
                                                <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden">
                                                <?php if ($user['avatar']): ?>
                                                <button type="button" class="btn btn-outline btn-danger">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="name">Full Name</label>
                                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="bio">Biography</label>
                                        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        <?php elseif ($activeTab === 'security'): ?>
                            <div class="settings-panel">
                                <h3 class="settings-panel-title">Security</h3>
                                <p class="settings-panel-description">Manage your password and account security settings.</p>
                                
                                <form method="POST" action="settings.php" class="settings-form">
                                    <input type="hidden" name="action" value="update_security">
                                    
                                    <div class="security-info">
                                        <div class="security-item">
                                            <div class="security-item-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="security-item-content">
                                                <h4>Last Password Change</h4>
                                                <p><?php echo htmlspecialchars($user['security']['last_password_change']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="security-item">
                                            <div class="security-item-icon">
                                                <i class="fas fa-desktop"></i>
                                            </div>
                                            <div class="security-item-content">
                                                <h4>Active Sessions</h4>
                                                <p><?php echo htmlspecialchars($user['security']['active_sessions']); ?> active devices</p>
                                            </div>
                                            <a href="#" class="btn btn-text">Manage</a>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password">
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" id="new_password" name="new_password">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="two_factor_auth" name="two_factor_auth" <?php echo $user['security']['two_factor_auth'] ? 'checked' : ''; ?>>
                                            <label for="two_factor_auth">Enable Two-Factor Authentication</label>
                                        </div>
                                        <p class="form-help">Two-factor authentication adds an extra layer of security to your account.</p>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Update Security</button>
                                    </div>
                                </form>
                            </div>
                        <?php elseif ($activeTab === 'notifications'): ?>
                            <div class="settings-panel">
                                <h3 class="settings-panel-title">Notification Preferences</h3>
                                <p class="settings-panel-description">Customize how you want to be notified about activities.</p>
                                
                                <form method="POST" action="settings.php" class="settings-form">
                                    <input type="hidden" name  class="settings-form">
                                    <input type="hidden" name="action" value="update_notifications">
                                    
                                    <div class="notification-section">
                                        <h4 class="notification-section-title">Email Notifications</h4>
                                        
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="email_course_updates" name="email_course_updates" <?php echo $user['notifications']['email_course_updates'] ? 'checked' : ''; ?>>
                                                <label for="email_course_updates">Course Updates</label>
                                            </div>
                                            <p class="form-help">Receive emails when new content is added to your courses.</p>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="email_new_messages" name="email_new_messages" <?php echo $user['notifications']['email_new_messages'] ? 'checked' : ''; ?>>
                                                <label for="email_new_messages">New Messages</label>
                                            </div>
                                            <p class="form-help">Receive emails when you get new messages.</p>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="email_reminders" name="email_reminders" <?php echo $user['notifications']['email_reminders'] ? 'checked' : ''; ?>>
                                                <label for="email_reminders">Reminders & Deadlines</label>
                                            </div>
                                            <p class="form-help">Receive email reminders for course deadlines and events.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-section">
                                        <h4 class="notification-section-title">Other Notifications</h4>
                                        
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="browser_notifications" name="browser_notifications" <?php echo $user['notifications']['browser_notifications'] ? 'checked' : ''; ?>>
                                                <label for="browser_notifications">Browser Notifications</label>
                                            </div>
                                            <p class="form-help">Receive notifications in your browser while on the platform.</p>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="sms_notifications" name="sms_notifications" <?php echo $user['notifications']['sms_notifications'] ? 'checked' : ''; ?>>
                                                <label for="sms_notifications">SMS Notifications</label>
                                            </div>
                                            <p class="form-help">Receive SMS notifications for important events.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
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
            
            // Avatar preview
            const avatarInput = document.getElementById('avatar-input');
            if (avatarInput) {
                avatarInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const avatarPreview = document.querySelector('.avatar-preview');
                            avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview">`;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Security form validation
            const securityForm = document.querySelector('form[name="action"][value="update_security"]');
            if (securityForm) {
                securityForm.addEventListener('submit', function(event) {
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (newPassword && newPassword !== confirmPassword) {
                        event.preventDefault();
                        alert('Passwords do not match.');
                    }
                });
            }
            
            // Ensure form-actions are visible on scroll
            const formActions = document.querySelectorAll('.form-actions');
            if (formActions.length > 0) {
                window.addEventListener('resize', function() {
                    if (window.innerWidth <= 768) {
                        formActions.forEach(function(formAction) {
                            formAction.classList.add('sticky-bottom');
                        });
                    } else {
                        formActions.forEach(function(formAction) {
                            formAction.classList.remove('sticky-bottom');
                        });
                    }
                });
                
                // Initial check
                if (window.innerWidth <= 768) {
                    formActions.forEach(function(formAction) {
                        formAction.classList.add('sticky-bottom');
                    });
                }
            }
        });
    </script>
</body>
</html>
