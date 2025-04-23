<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect non-admin users
    header('Location: dashboard.php');
    exit;
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if($id === false) {
        header("Location: users.php?error=Invalid user ID");
        exit;
    }
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: users.php?success=User deleted successfully");
        exit;
    } else {
        header("Location: users.php?error=Error deleting user: " . $stmt->error);
        exit;
    }
    
    $stmt->close();
} else {
    header("Location: users.php");
    exit;
}

$conn->close();
?>
