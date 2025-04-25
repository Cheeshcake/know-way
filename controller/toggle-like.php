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
    header('Location: ../view/index.html');
    exit;
}

// Check if course_id is provided
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    // Redirect back with error
    header('Location: ../view/dashboard.php?error=' . urlencode('Course ID is required'));
    exit;
}

$course_id = $_POST['course_id'];
$user_id = $_SESSION['user_id'];

// Check if the course exists
$course_check = "SELECT id FROM courses WHERE id = ?";
$check_stmt = $conn->prepare($course_check);
$check_stmt->bind_param("i", $course_id);
$check_stmt->execute();
$course_result = $check_stmt->get_result();

if ($course_result->num_rows === 0) {
    // Course doesn't exist, redirect back with error
    header('Location: ../view/dashboard.php?error=' . urlencode('Course not found'));
    exit;
}

// Check if user already liked this course
$like_check = "SELECT id FROM course_likes WHERE course_id = ? AND user_id = ?";
$like_stmt = $conn->prepare($like_check);
$like_stmt->bind_param("ii", $course_id, $user_id);
$like_stmt->execute();
$like_result = $like_stmt->get_result();

if ($like_result->num_rows > 0) {
    // User already liked the course, remove the like
    $unlike_sql = "DELETE FROM course_likes WHERE course_id = ? AND user_id = ?";
    $unlike_stmt = $conn->prepare($unlike_sql);
    $unlike_stmt->bind_param("ii", $course_id, $user_id);
    
    if ($unlike_stmt->execute()) {
        // Success - redirect back to course details
        header('Location: ../view/course-details.php?id=' . $course_id . '&success=' . urlencode('Course unliked'));
    } else {
        // Error - redirect with error message
        header('Location: ../view/course-details.php?id=' . $course_id . '&error=' . urlencode('Failed to unlike course: ' . $conn->error));
    }
    
    $unlike_stmt->close();
} else {
    // User hasn't liked the course yet, add a like
    $like_sql = "INSERT INTO course_likes (course_id, user_id, created_at) VALUES (?, ?, NOW())";
    $add_stmt = $conn->prepare($like_sql);
    $add_stmt->bind_param("ii", $course_id, $user_id);
    
    if ($add_stmt->execute()) {
        // Success - redirect back to course details
        header('Location: ../view/course-details.php?id=' . $course_id . '&success=' . urlencode('Course liked'));
    } else {
        // Error - redirect with error message
        header('Location: ../view/course-details.php?id=' . $course_id . '&error=' . urlencode('Failed to like course: ' . $conn->error));
    }
    
    $add_stmt->close();
}

$check_stmt->close();
$like_stmt->close();
$conn->close();
?> 