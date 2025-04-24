<?php
// delete-user.php - Delete a user from the database

// Include database connection
include '../config/db.php';

// Check if user is admin (to be implemented with sessions)
// For now, we'll assume the user is an admin

// Check if user_id is provided
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    // Redirect back with error
    header('Location: ../view/users.php?error=1&message=' . urlencode('User ID is required.'));
    exit;
}

$user_id = (int)$_POST['user_id'];

// Check if user exists
$check_sql = "SELECT id FROM users WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    // User not found, redirect with error
    header('Location: ../view/users.php?error=1&message=' . urlencode('User not found.'));
    exit;
}

// Delete the user
$delete_sql = "DELETE FROM users WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    // Redirect with success message
    header('Location: ../view/users.php?success=1&message=' . urlencode('User deleted successfully.'));
    exit;
} else {
    // Redirect with error message
    header('Location: ../view/users.php?error=1&message=' . urlencode('Failed to delete user: ' . $conn->error));
    exit;
}
?> 