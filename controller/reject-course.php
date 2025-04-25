<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header('Location: ../view/index.html');
    exit;
}

// Check if course ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    // Redirect back with error
    header('Location: ../view/pending-courses.php?error=' . urlencode('Course ID is required'));
    exit;
}

$course_id = $_POST['id'];
$rejection_reason = isset($_POST['reason']) ? $_POST['reason'] : '';

// Update course status to draft (rejected)
$reject_sql = "UPDATE courses 
               SET status = 'draft',
                   rejection_reason = ? 
               WHERE id = ?";

$stmt = $conn->prepare($reject_sql);
$stmt->bind_param("si", $rejection_reason, $course_id);

if ($stmt->execute()) {
    // Success - redirect with success message
    header('Location: ../view/pending-courses.php?success=' . urlencode('Course rejected successfully'));
} else {
    // Error - redirect with error message
    header('Location: ../view/pending-courses.php?error=' . urlencode('Failed to reject course: ' . $conn->error));
}

$stmt->close();
$conn->close();
?> 