<?php
// settings.php - User Settings Page (English version)

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

// Include database connection
include '../config/db.php';

// Initialize error messages
$errorMsg = '';

// Check user role and redirect if necessary
$isAdmin = $_SESSION['role'] === 'admin';
if (!$isAdmin) {
    // For non-admin users, redirect to a different settings page if needed
    // For now, we'll use the same settings page for all users
}

// Get user data from database
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // User not found, redirect to login
    header('Location: index.html');
    exit;
}

// For demonstration purposes - hardcoded security data
// In a real application, this would come from database
$user['security'] = [
    'two_factor_auth' => $user['two_factor_auth'] ?? false,
    'last_password_change' => $user['last_password_change'] ?? date('d/m/Y'),
    'active_sessions' => 1
];

$user['courses'] = [
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
];

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_general') {
        // Update user's general information
        $username = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? null;
        $bio = $_POST['bio'] ?? null;
        
        // Check if avatar should be removed
        $remove_avatar = isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1';
        
        // Handle avatar upload if provided
        $avatar = null;
        $avatar_query_part = "";
        
        if ($remove_avatar) {
            // Remove avatar from database and delete file if exists
            if (isset($user['avatar']) && file_exists($user['avatar'])) {
                unlink($user['avatar']);
            }
            $avatar_query_part = ", avatar = NULL";
        } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/avatars/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                // Delete old avatar if exists
                if (isset($user['avatar']) && file_exists($user['avatar'])) {
                    unlink($user['avatar']);
                }
                $avatar = $uploadPath;
                $avatar_query_part = ", avatar = ?";
            }
        }
        
        // Prepare SQL query
        $update_query = "UPDATE users SET username = ?, email = ?, phone = ?, bio = ?" . $avatar_query_part . " WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        
        if (!empty($avatar_query_part) && strpos($avatar_query_part, "= ?") !== false) {
            // If we're setting avatar to a new value
            $stmt->bind_param("sssssi", $username, $email, $phone, $bio, $avatar, $user_id);
        } else {
            // If we're removing avatar or not changing it
            $stmt->bind_param("ssssi", $username, $email, $phone, $bio, $user_id);
        }
        
        if ($stmt->execute()) {
            // Update session with new username
            $_SESSION['username'] = $username;
            header('Location: settings.php?tab=general&success=1');
            exit;
        } else {
            header('Location: settings.php?tab=general&error=1');
            exit;
        }
    } 
    elseif ($_POST['action'] === 'update_security') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $two_factor_auth = isset($_POST['two_factor_auth']) ? 1 : 0;
        
        // Validate current password
        $password_query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($password_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        $password_updated = false;
        
        // Update password if provided and valid
        if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
            if (password_verify($current_password, $user_data['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $password_updated = true;
                    }
                } else {
                    header('Location: settings.php?tab=security&error=passwords_mismatch');
                    exit;
                }
            } else {
                header('Location: settings.php?tab=security&error=invalid_password');
                exit;
            }
        }
        
        // Update two-factor authentication setting
        $check_column = "SHOW COLUMNS FROM users LIKE 'two_factor_auth'";
        $result = $conn->query($check_column);
        
        if ($result->num_rows > 0) {
            $update_2fa_query = "UPDATE users SET two_factor_auth = ? WHERE id = ?";
            $stmt = $conn->prepare($update_2fa_query);
            $stmt->bind_param("ii", $two_factor_auth, $user_id);
            
            if ($stmt->execute() || $password_updated) {
                header('Location: settings.php?tab=security&success=1');
                exit;
            } else {
                header('Location: settings.php?tab=security&error=1');
                exit;
            }
        } else {
            // Two-factor auth column doesn't exist yet, but password might have been updated
            if ($password_updated) {
                header('Location: settings.php?tab=security&success=1');
                exit;
            } else {
                header('Location: settings.php?tab=security&error=column_missing');
                exit;
            }
        }
    }
}

$activeTab = $_GET['tab'] ?? 'general';
// Ensure activeTab is only general or security
if ($activeTab !== 'general' && $activeTab !== 'security') {
    $activeTab = 'general';
}
$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;
$showError = isset($_GET['error']);

// Process error messages
if ($showError) {
    $errorType = $_GET['error'];
    if ($errorType === 'passwords_mismatch') {
        $errorMsg = 'The passwords you entered do not match.';
    } elseif ($errorType === 'invalid_password') {
        $errorMsg = 'The current password you entered is incorrect.';
    } elseif ($errorType === 'column_missing') {
        $errorMsg = 'The two-factor authentication feature is not available yet.';
    } else {
        $errorMsg = 'An error occurred while saving your settings. Please try again.';
    }
}

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $user['username']);
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
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
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
                    <li><a href="admin.php"><span class="nav-icon dashboard-icon"></span>Dashboard</a></li>
                    <li><a href="pending-courses.php"><span class="nav-icon courses-icon"></span>Pending Courses</a></li>
                    <li><a href="users.php"><span class="nav-icon users-icon"></span>Users</a></li>
                    <li class="active"><a href="settings.php"><span class="nav-icon settings-icon"></span>Settings</a></li>
                    
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.html" class="logout-btn">Sign Out</a>
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
                    <div class="admin-profile">
                    <div class="admin-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-weight: 600; flex-shrink: 0; text-align: center;">
                    <?php if (isset($user['avatar']) && $user['avatar']): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; display: block; margin: 0; padding: 0;">
                            <?php else: ?>
                                <span style="font-size: 16px;"><?php echo htmlspecialchars($initials); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="admin-name"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if ($showSuccess): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Your settings have been successfully updated.
                    <button class="close-alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <?php if ($showError): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($errorMsg); ?>
                    <button class="close-alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <div class="settings-container">
                    <div class="settings-sidebar">
                        <div class="user-info-card">
                            <div class="user-avatar large">
                                <?php if (isset($user['avatar']) && $user['avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                            </div>
                            <h3 class="user-name"><?php echo htmlspecialchars($user['username']); ?></h3>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        
                        <nav class="settings-nav">
                            <a href="?tab=general" class="settings-nav-item <?php echo $activeTab === 'general' ? 'active' : ''; ?>">
                                <span class="nav-icon dashboard-icon"></span>
                                General Information
                            </a>
                            <a href="?tab=security" class="settings-nav-item <?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                                <span class="nav-icon settings-icon"></span>
                                Security
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
                                                <?php if (isset($user['avatar']) && $user['avatar']): ?>
                                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <?php echo htmlspecialchars($initials); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="avatar-actions">
                                                <label for="avatar-input" class="btn btn-outline">
                                                    Upload
                                                </label>
                                                <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden">
                                                <?php if (isset($user['avatar']) && $user['avatar']): ?>
                                                <button type="button" class="btn btn-outline btn-danger" id="remove-avatar-btn">
                                                    Remove
                                                </button>
                                                <input type="hidden" name="remove_avatar" id="remove-avatar" value="0">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="name">Full Name</label>
                                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" id="email" name="email"  value="<?php echo htmlspecialchars($user['email']); ?>" required readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="bio">Biography</label>
                                        <textarea id="bio" name="bio" rows="4"><?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="submit-btn">Save Changes</button>
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
                                                <span class="nav-icon dashboard-icon"></span>
                                            </div>
                                            <div class="security-item-content">
                                                <h4>Last Password Change</h4>
                                                <p><?php echo htmlspecialchars($user['security']['last_password_change']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="security-item">
                                            <div class="security-item-icon">
                                                <span class="nav-icon users-icon"></span>
                                            </div>
                                            <div class="security-item-content">
                                                <h4>Active Sessions</h4>
                                                <p><?php echo htmlspecialchars($user['security']['active_sessions']); ?> active devices</p>
                                            </div>
                                            <a href="#" class="btn-text">Manage</a>
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
                                        <button type="submit" class="submit-btn">Update Security</button>
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
            
            // Avatar removal
            const removeAvatarBtn = document.getElementById('remove-avatar-btn');
            if (removeAvatarBtn) {
                removeAvatarBtn.addEventListener('click', function() {
                    const avatarPreview = document.querySelector('.avatar-preview');
                    const removeAvatarInput = document.getElementById('remove-avatar');
                    
                    // Update the hidden input value
                    removeAvatarInput.value = '1';
                    
                    // Replace avatar image with initials placeholder
                    const initials = '<?php echo htmlspecialchars($initials); ?>';
                    avatarPreview.innerHTML = `<div class="avatar-placeholder">${initials}</div>`;
                    
                    // Hide the remove button
                    this.style.display = 'none';
                });
            }
            
            // Form Validation
            
            // General form validation
            const generalForm = document.querySelector('form input[name="action"][value="update_general"]').closest('form');
            if (generalForm) {
                generalForm.addEventListener('submit', function(event) {
                    const name = document.getElementById('name').value.trim();
                    const email = document.getElementById('email').value.trim();
                    
                    if (name === '') {
                        event.preventDefault();
                        showInlineError('name', 'Name is required');
                        return false;
                    }
                    
                    if (email === '') {
                        event.preventDefault();
                        showInlineError('email', 'Email is required');
                        return false;
                    }
                    
                    if (!isValidEmail(email)) {
                        event.preventDefault();
                        showInlineError('email', 'Please enter a valid email address');
                        return false;
                    }
                });
            }
            
            // Security form validation
            const securityForm = document.querySelector('form input[name="action"][value="update_security"]').closest('form');
            if (securityForm) {
                securityForm.addEventListener('submit', function(event) {
                    const currentPassword = document.getElementById('current_password').value;
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    // Only validate if attempting to change password
                    if (newPassword || confirmPassword) {
                        if (!currentPassword) {
                            event.preventDefault();
                            showInlineError('current_password', 'Current password is required');
                            return false;
                        }
                        
                        if (newPassword !== confirmPassword) {
                            event.preventDefault();
                            showInlineError('confirm_password', 'Passwords do not match');
                            return false;
                        }
                        
                        if (newPassword.length < 8) {
                            event.preventDefault();
                            showInlineError('new_password', 'Password must be at least 8 characters');
                            return false;
                        }
                    }
                });
            }
            
            // Helper functions
            function showInlineError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = message;
                
                // Remove any existing error messages
                const existingError = field.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
                
                // Add error class to input
                field.classList.add('input-error');
                
                // Insert error message after the input
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
                
                // Clear error when input changes
                field.addEventListener('input', function() {
                    const error = this.parentNode.querySelector('.field-error');
                    if (error) {
                        error.remove();
                        this.classList.remove('input-error');
                    }
                }, { once: true });
            }
            
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>