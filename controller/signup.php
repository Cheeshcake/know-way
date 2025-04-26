<?php
// signup.php - Handle user signup

// Include database connection
include '../config/db.php';

// Initialize variables
$error = '';
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fullname = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['terms']);

    // Validate input
    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$agree_terms) {
        $error = 'You must agree to the Terms and Conditions.';
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = 'Email address is already registered.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Set default role to 'learner'
            $role = 'learner';
            
            // Insert new user
            $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $fullname, $email, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                $success = true;
                
                // Start session and set user data
                session_start();
                $_SESSION['user_id'] = $insert_stmt->insert_id;
                $_SESSION['username'] = $fullname;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                
                // Redirect to learner dashboard (to be created)
                header('Location: ../view/dashboard.php');
                exit;
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
        }
    }
}

// If not redirected due to success, return to signup page with error
if (!$success) {
    header('Location: ../view/signup.html?error=' . urlencode($error));
    exit;
}
?> 