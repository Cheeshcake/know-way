<?php
// login.php - Handle user login

// Include database connection
include '../config/db.php';

// Initialize variables
$error = '';
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        // Get user from database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $success = true;
                
                // Start session
                session_start();
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Set cookie if remember me is checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    // In a real application, you'd store this token in the database
                    // For now, just set the cookie
                    setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ../view/admin.php');
                } else {
                    // Redirect to learner dashboard
                    header('Location: ../view/dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// If not redirected due to success, return to login page with error
if (!$success) {
    header('Location: ../view/index.html?error=' . urlencode($error));
    exit;
}
?> 