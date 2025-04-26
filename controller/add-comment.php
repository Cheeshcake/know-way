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

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Not a POST request, redirect back
    header('Location: ../view/dashboard.php');
    exit;
}

// Check if required fields are provided
if (!isset($_POST['course_id']) || empty($_POST['course_id']) || !isset($_POST['comment']) || empty($_POST['comment'])) {
    // Missing required fields, redirect back with error
    header('Location: ../view/dashboard.php?error=' . urlencode('Course ID and comment are required'));
    exit;
}

$course_id = $_POST['course_id'];
$comment_text = trim($_POST['comment']);
$user_id = $_SESSION['user_id'];

// Check if the course exists
$course_check = "SELECT id, status FROM courses WHERE id = ?";
$check_stmt = $conn->prepare($course_check);
$check_stmt->bind_param("i", $course_id);
$check_stmt->execute();
$course_result = $check_stmt->get_result();

if ($course_result->num_rows === 0) {
    // Course doesn't exist, redirect back with error
    header('Location: ../view/dashboard.php?error=' . urlencode('Course not found'));
    exit;
}

$course = $course_result->fetch_assoc();

// Only allow comments on published courses
if ($course['status'] !== 'published' && $_SESSION['role'] !== 'admin') {
    // Course is not published and user is not admin, redirect back with error
    header('Location: ../view/dashboard.php?error=' . urlencode('You can only comment on published courses'));
    exit;
}

// Insert the comment
$comment_sql = "INSERT INTO course_comments (course_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
$comment_stmt = $conn->prepare($comment_sql);
$comment_stmt->bind_param("iis", $course_id, $user_id, $comment_text);

if ($comment_stmt->execute()) {
    // Success - redirect back to course details
    header('Location: ../view/course-details.php?id=' . $course_id . '&success=' . urlencode('Comment added successfully'));
} else {
    // Error - redirect with error message
    header('Location: ../view/course-details.php?id=' . $course_id . '&error=' . urlencode('Failed to add comment: ' . $conn->error));
}

$check_stmt->close();
$comment_stmt->close();
$conn->close();
?> 