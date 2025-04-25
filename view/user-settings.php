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
$role = $_SESSION['role'] ?? 'user';

// Only allow regular users to access this page
if ($role !== 'user' && $role !== 'learner') {
    header('Location: dashboard.php');
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get avatar path and initials
$user_avatar = isset($user['avatar']) ? $user['avatar'] : '';
$initials = '';
$name_parts = explode(' ', $username);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}
if (empty($initials)) {
    $initials = substr($username, 0, 1);
}

// Process general information update
if (isset($_POST['update_general'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $error = '';
    $success = '';

    // Validate username (alphanumeric, 3-30 chars)
    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $new_username)) {
        $error = "Username must be 3-30 characters and contain only letters, numbers, and underscores.";
    } 
    // Check if username is changed and already exists
    else if ($new_username !== $user['username']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $new_username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Username already exists. Please choose another one.";
        }
        $stmt->close();
    }
    
    // Validate email
    if (empty($error) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    // Check if email is changed and already exists
    else if (empty($error) && $new_email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already exists. Please choose another one.";
        }
        $stmt->close();
    }
    
    // Handle profile picture upload
    $avatar_query_part = "";
    $remove_avatar = isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1';
    
    if ($remove_avatar) {
        // Remove avatar from database and delete file if exists
        if (!empty($user['avatar']) && file_exists($user['avatar'])) {
            unlink($user['avatar']);
        }
        $avatar_query_part = ", avatar = NULL";
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filetype = strtolower($filetype);
        
        // Check if file type is allowed
        if (in_array($filetype, $allowed)) {
            $new_filename = uniqid('avatar_') . '.' . $filetype;
            $upload_dir = '../uploads/avatars/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Delete old avatar if exists
                if (!empty($user['avatar']) && file_exists($user['avatar'])) {
                    unlink($user['avatar']);
                }
                
                $avatar_query_part = ", avatar = ?";
                $avatar_path = $upload_path;
            } else {
                $error = "Error uploading profile picture.";
            }
        } else {
            $error = "Invalid file type. Allowed types: JPG, JPEG, PNG, GIF.";
        }
    }
    
    // If no errors, update user information
    if (empty($error)) {
        // Prepare SQL query based on whether avatar is being updated
        if (!empty($avatar_query_part) && strpos($avatar_query_part, "= ?") !== false) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, bio = ? $avatar_query_part WHERE id = ?");
            $stmt->bind_param("sssssi", $new_username, $new_email, $phone, $bio, $avatar_path, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, bio = ? $avatar_query_part WHERE id = ?");
            $stmt->bind_param("ssssi", $new_username, $new_email, $phone, $bio, $user_id);
        }
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            
            // Update session data
            $_SESSION['username'] = $new_username;
            
            // Reload user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    }
}

// Process security settings update
if (isset($_POST['update_security'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $error = '';
    $success = '';
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    }
    
    // Validate new password
    if (empty($error) && strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters.";
    }
    
    // Check if passwords match
    if (empty($error) && $new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    }
    
    // If no errors, update password
    if (empty($error)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = "Password updated successfully!";
        } else {
            $error = "Error updating password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Account Settings</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        .settings-container {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }
        
        .settings-header {
            padding: 24px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .settings-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--dark);
        }
        
        .settings-header p {
            margin: 8px 0 0;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .settings-body {
            padding: 24px;
        }
        
        .form-row {
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }
        
        .form-group {
            flex: 1;
            min-width: 250px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .form-help {
            display: block;
            margin-top: 6px;
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .profile-upload {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .profile-picture {
            width: 100px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--light-gray);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 24px;
            font-size: 24px;
            color: var(--gray);
        }
        
        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .upload-btn {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            border: 1px solid var(--light-gray);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .upload-btn:hover {
            background-color: var(--very-light-gray);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary);
            opacity: 0.8;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background-color: #e6f7f0;
            color: #0d7f55;
            border: 1px solid #bee5d3;
        }
        
        .alert-danger {
            background-color: #feeeed;
            color: #d03c3c;
            border: 1px solid #fcd0cd;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 24px;
        }
        
        .tab {
            padding: 16px 24px;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            position: relative;
            transition: all 0.2s;
        }
        
        .tab.active {
            color: var(--primary);
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
                        <a href="user-settings.php" class="nav-link active">
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
            <h1 class="greeting">Account Settings</h1>
            <p class="subheading">Manage your account information and security</p>
        </div>
        
        <!-- Settings content -->
        <div class="tabs">
            <div class="tab active" data-tab="general">General Information</div>
            <div class="tab" data-tab="security">Security</div>
        </div>
        
        <!-- General Information Tab -->
        <div class="tab-content active" id="general">
            <?php if (isset($error) && !empty($error) && isset($_POST['update_general'])): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success) && !empty($success) && isset($_POST['update_general'])): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="settings-container">
                <div class="settings-header">
                    <h2>Profile Information</h2>
                    <p>Update your profile information and picture</p>
                </div>
                <div class="settings-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="profile-upload">
                            <div class="profile-picture">
                                <?php if (!empty($user_avatar) && file_exists($user_avatar)): ?>
                                    <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Profile Picture">
                                <?php else: ?>
                                    <?php echo htmlspecialchars($initials); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" name="avatar" id="avatar" style="display: none;">
                                <label for="avatar" class="upload-btn">Upload New Picture</label>
                                <p class="form-help">Recommended: Square image, at least 200x200 pixels.</p>
                                <?php if (!empty($user_avatar) && file_exists($user_avatar)): ?>
                                <div style="margin-top: 10px;">
                                    <label class="upload-btn" style="background-color: #ffd5d5; color: #d03c3c; border-color: #fcd0cd;" for="remove_avatar_check">
                                        <input type="checkbox" id="remove_avatar_check" style="margin-right: 5px;">
                                        Remove Picture
                                    </label>
                                    <input type="hidden" name="remove_avatar" id="remove_avatar" value="0">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                <span class="form-help">Your unique username for logging in</span>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <span class="form-help">Your email address for notifications</span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_general" class="btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Security Tab -->
        <div class="tab-content" id="security">
            <?php if (isset($error) && !empty($error) && isset($_POST['update_security'])): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success) && !empty($success) && isset($_POST['update_security'])): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="settings-container">
                <div class="settings-header">
                    <h2>Change Password</h2>
                    <p>Update your password to keep your account secure</p>
                </div>
                <div class="settings-body">
                    <form action="" method="post">
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <span class="form-help">At least 8 characters</span>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_security" class="btn-primary">Update Password</button>
                    </form>
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
        
        // Tabs functionality
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Profile picture preview
        const profilePicture = document.getElementById('avatar');
        if (profilePicture) {
            profilePicture.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Create image element if it doesn't exist
                        let profileImg = document.querySelector('.profile-picture img');
                        if (!profileImg) {
                            profileImg = document.createElement('img');
                            document.querySelector('.profile-picture').innerHTML = '';
                            document.querySelector('.profile-picture').appendChild(profileImg);
                        }
                        // Set image source to file preview
                        profileImg.src = e.target.result;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
        
        // Handle remove profile picture checkbox
        const removeAvatarCheck = document.getElementById('remove_avatar_check');
        const removeAvatarInput = document.getElementById('remove_avatar');
        if (removeAvatarCheck && removeAvatarInput) {
            removeAvatarCheck.addEventListener('change', function() {
                if (this.checked) {
                    removeAvatarInput.value = '1';
                    // Replace image with initial
                    const profilePicture = document.querySelector('.profile-picture');
                    profilePicture.innerHTML = '<?php echo htmlspecialchars($initials); ?>';
                } else {
                    removeAvatarInput.value = '0';
                    // If there's an original image, restore it
                    if ('<?php echo !empty($user_avatar) && file_exists($user_avatar); ?>') {
                        const profilePicture = document.querySelector('.profile-picture');
                        profilePicture.innerHTML = '<img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Profile Picture">';
                    }
                }
            });
        }
    </script>
</body>
</html>
