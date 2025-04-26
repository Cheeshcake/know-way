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

// Get the course ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to appropriate page based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$course_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Query to get course details
$course_sql = "SELECT c.*, u.username as creator_name 
               FROM courses c 
               JOIN users u ON c.creator_id = u.id
               WHERE c.id = ?";

$stmt = $conn->prepare($course_sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Course not found, redirect
    if ($user_role === 'admin') {
        header('Location: admin.php?error=' . urlencode('Course not found'));
    } else {
        header('Location: dashboard.php?error=' . urlencode('Course not found'));
    }
    exit;
}

$course = $result->fetch_assoc();

// Check if user is allowed to view this course
if ($course['status'] !== 'published' && $user_role !== 'admin' && $course['creator_id'] !== $user_id) {
    // Not published and user is not admin or creator, redirect
    header('Location: dashboard.php?error=' . urlencode('You are not authorized to view this course'));
    exit;
}

// Check if user has liked this course
$liked_sql = "SELECT * FROM course_likes WHERE course_id = ? AND user_id = ?";
$liked_stmt = $conn->prepare($liked_sql);
$liked_stmt->bind_param("ii", $course_id, $user_id);
$liked_stmt->execute();
$user_liked = ($liked_stmt->get_result()->num_rows > 0);

// Get total likes for this course
$likes_count_sql = "SELECT COUNT(*) as total FROM course_likes WHERE course_id = ?";
$likes_count_stmt = $conn->prepare($likes_count_sql);
$likes_count_stmt->bind_param("i", $course_id);
$likes_count_stmt->execute();
$likes_count = $likes_count_stmt->get_result()->fetch_assoc()['total'];

// Get comments for this course
$comments_sql = "SELECT cc.*, u.username, u.avatar 
                 FROM course_comments cc
                 JOIN users u ON cc.user_id = u.id
                 WHERE cc.course_id = ?
                 ORDER BY cc.created_at DESC";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $course_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Get quizzes for this course
$quizzes_sql = "SELECT * FROM course_quizzes WHERE course_id = ?";
$quizzes_stmt = $conn->prepare($quizzes_sql);
$quizzes_stmt->bind_param("i", $course_id);
$quizzes_stmt->execute();
$quizzes_result = $quizzes_stmt->get_result();

// Success or error messages
$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - KnowWay</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary-light: rgba(79, 70, 229, 0.1);
            --primary-lighter: rgba(79, 70, 229, 0.05);
        }
        
        .course-container {
            max-width: 1300px;
            width: 100%;
            /* margin: 0 auto; */
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: #4f46e5;
            padding: 8px 12px;
            font-weight: 500;
            margin-bottom: 24px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background-color: var(--primary-lighter);
        }

        .back-icon {
            width: 18px;
            height: 18px;
            display: inline-block;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Enhanced Alert Styling */
        .alert {
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* Enhanced Course Header */
        .course-header {
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
            position: relative;
        }

        .course-title {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #111827;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 600px
        }

        .course-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .course-creator {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .course-creator:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .course-date {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .course-date:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .course-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-published {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .status-published:before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23198754" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .status-pending:before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .status-draft {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .status-draft:before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 6px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Enhanced Course Image */
        .course-image {
            width: 100%;
            height: 450px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background-size: fill;
            background-position: center;
            background-repeat: no-repeat;
            object-fit: fill;
            position: relative;
        }

        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .course-image:hover img {
            transform: scale(1.03);
        }

        /* Course Video Styles */
        .course-video-container {
            margin-bottom: 30px;
        }

        .video-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: #111827;
        }

        .course-video {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .video-responsive {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
        }

        .video-responsive iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
        }

        .uploaded-video {
            width: 100%;
            max-height: 500px;
            border-radius: 12px;
            background-color: #000;
        }

        /* Enhanced Course Actions */
        .course-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .like-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 50px;
            padding: 10px 18px;
            cursor: pointer;
            transition: all 0.3s;
            color: #4b5563;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .like-btn.liked {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .like-btn:hover {
            background-color: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .like-btn.liked:hover {
            background-color: rgba(239, 68, 68, 0.15);
        }

        .like-icon {
            width: 18px;
            height: 18px;
            display: inline-block;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Enhanced Course Description */
        .course-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4b5563;
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 12px;
            border-left: 4px solid #4f46e5;
        }

        /* Enhanced Tabs */
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            position: relative;
        }

        .tab {
            padding: 14px 24px;
            cursor: pointer;
            color: #6b7280;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            position: relative;
            z-index: 1;
        }

        .tab:hover {
            color: #4f46e5;
        }

        .tab.active {
            color: #4f46e5;
        }
        
        .tab.active:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #4f46e5;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-content.active {
            display: block;
        }

        /* Enhanced Comments Section */
        .comments-section {
            margin-top: 20px;
        }

        .comment-form {
            margin-bottom: 40px;
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 12px;
        }

        .comment-input {
            width: 100%;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 16px;
            transition: all 0.3s;
            resize: vertical;
            min-height: 100px;
            background-color: white;
        }

        .comment-input:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .comment-submit {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .comment-submit:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
        }

        .comments-container {
            margin-top: 30px;
        }

        .comment {
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 0;
            transition: transform 0.3s;
        }
        
        .comment:hover {
            transform: translateX(5px);
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .comment-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #e5e7eb;
            margin-right: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            color: #4b5563;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .comment-user {
            font-weight: 600;
            color: #111827;
            font-size: 1.05rem;
            display: block;
            margin-bottom: 2px;
        }

        .comment-date {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .comment-body {
            font-size: 1rem;
            line-height: 1.7;
            color: #4b5563;
            padding-left: 64px;
        }

        .no-comments {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            background-color: #f9fafb;
            border-radius: 12px;
            border: 1px dashed #e5e7eb;
        }

        /* Enhanced Quiz Styles */
        .quiz-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            padding: 24px;
            transition: all 0.3s;
            border: 1px solid #f3f4f6;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: #e5e7eb;
        }

        .quiz-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #111827;
        }

        .quiz-description {
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .start-quiz-btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .start-quiz-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .start-quiz-btn:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
        }

        /* Enhanced Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            background-color: #f9fafb;
            border-radius: 12px;
            color: #6b7280;
            margin: 20px 0;
            border: 1px dashed #e5e7eb;
        }
        
        .empty-state:before {
            content: '';
            display: block;
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23d1d5db" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .empty-state h3 {
            margin-bottom: 12px;
            color: #374151;
            font-size: 1.3rem;
        }

        .empty-state p {
            margin-bottom: 0;
            font-size: 1rem;
        }

        /* Enhanced Admin Actions */
        .admin-actions {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin: 40px 0;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .admin-actions h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #111827;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .admin-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .admin-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .approve-btn {
            background-color: #10b981;
            color: white;
            border: none;
        }
        
        .approve-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .approve-btn:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
        }

        .reject-btn {
            background-color: #ef4444;
            color: white;
            border: none;
        }
        
        .reject-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .reject-btn:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.1);
        }

        .add-quiz-btn {
            background-color: #4f46e5;
            color: white;
            border: none;
        }
        
        .add-quiz-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .add-quiz-btn:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
        }

        .edit-btn {
            background-color: #6b7280;
            color: white;
            border: none;
        }
        
        .edit-btn:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .edit-btn:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(107, 114, 128, 0.1);
        }
        
        textarea[name="reason"] {
            width: 100%;
            margin-top: 16px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 0.95rem;
            transition: all 0.3s;
            min-height: 100px;
            resize: vertical;
        }
        
        textarea[name="reason"]:focus {
            outline: none;
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .course-container {
                padding: 20px 16px;
                border-radius: 0;
                box-shadow: none;
            }
            
            .course-title {
                font-size: 1.8rem;
            }

            .course-image {
                height: 250px;
                border-radius: 12px;
            }

            .course-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .course-actions {
                flex-wrap: wrap;
            }

            .tab {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            
            .comment-body {
                padding-left: 0;
                margin-top: 12px;
            }
            
            .admin-buttons {
                flex-direction: column;
            }
            
            .admin-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="course-container">
        <a class="back-btn" href="pending-courses.php">
            <span class="back-icon"></span>
            Back to Courses
        </a>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" id="successAlert">
            <span><?= htmlspecialchars($success_message) ?></span>
            <button class="alert-close" onclick="closeAlert('successAlert')">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" id="errorAlert">
            <span><?= htmlspecialchars($error_message) ?></span>
            <button class="alert-close" onclick="closeAlert('errorAlert')">&times;</button>
        </div>
        <?php endif; ?>

        <div class="course-header">
            <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
            <div class="course-meta">
                <div class="course-creator">
                    <span>Created by: <?= htmlspecialchars($course['creator_name']) ?></span>
                </div>
                <div class="course-date">
                    <span>Created: <?= date('M d, Y', strtotime($course['created_at'])) ?></span>
                </div>
                <div class="course-status status-<?= $course['status'] ?>">
                    <?php 
                        switch($course['status']) {
                            case 'published':
                                echo 'Published';
                                break;
                            case 'pending':
                                echo 'Pending Approval';
                                break;
                            case 'draft':
                                echo 'Draft';
                                break;
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="course-image">
            <img src="uploads/<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" onerror="this.src='placeholder-course.png'">
        </div>

        <?php if (!empty($course['video'])): ?>
        <div class="course-video-container">
            <h3 class="video-title">Course Video</h3>
            <div class="course-video">
                <?php if ($course['video_type'] === 'youtube'): ?>
                    <?php 
                        // Extract YouTube video ID
                        $youtube_id = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $course['video'], $matches)) {
                            $youtube_id = $matches[1];
                        }
                    ?>
                    <div class="video-responsive">
                        <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?= $youtube_id ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                <?php elseif ($course['video_type'] === 'vimeo'): ?>
                    <?php 
                        // Extract Vimeo video ID
                        $vimeo_id = '';
                        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $course['video'], $matches)) {
                            $vimeo_id = $matches[1];
                        }
                    ?>
                    <div class="video-responsive">
                        <iframe width="100%" height="100%" src="https://player.vimeo.com/video/<?= $vimeo_id ?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                    </div>
                <?php elseif ($course['video_type'] === 'uploaded'): ?>
                    <video controls class="uploaded-video">
                        <source src="uploads/videos/<?= htmlspecialchars($course['video']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="course-actions">
            <form method="POST" action="../controller/toggle-like.php" id="likeForm">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <button type="submit" class="like-btn <?= $user_liked ? 'liked' : '' ?>">
                    <span class="like-icon"></span>
                    <span><?= $user_liked ? 'Liked' : 'Like' ?></span>
                    <span>(<?= $likes_count ?>)</span>
                </button>
            </form>
        </div>

        <div class="course-description">
            <?= nl2br(htmlspecialchars($course['description'])) ?>
        </div>

        <?php if ($user_role === 'admin' && $course['status'] === 'pending'): ?>
        <div class="admin-actions">
            <h3>Administrative Actions</h3>
            <div class="admin-buttons">
                <form method="POST" action="../controller/approve-course.php" onsubmit="return confirm('Are you sure you want to approve this course?');">
                    <input type="hidden" name="id" value="<?= $course['id'] ?>">
                    <button type="submit" class="admin-btn approve-btn">Approve Course</button>
                </form>
                
                <form method="POST" action="../controller/reject-course.php" onsubmit="return confirm('Are you sure you want to reject this course?');">
                    <input type="hidden" name="id" value="<?= $course['id'] ?>">
                    <button type="submit" class="admin-btn reject-btn">Reject Course</button>
                    <textarea name="reason" placeholder="Rejection reason (optional)"></textarea>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" data-tab="comments">Comments</div>
            <div class="tab" data-tab="quizzes">Quizzes</div>
        </div>

        <div class="tab-content active" id="comments-tab">
            <div class="comments-section">
                <form method="POST" action="../controller/add-comment.php" class="comment-form">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <textarea name="comment" class="comment-input" placeholder="Add a comment..." required></textarea>
                    <button type="submit" class="comment-submit">Post Comment</button>
                </form>

                <div class="comments-container">
                    <?php if ($comments_result->num_rows > 0): ?>
                        <?php while ($comment = $comments_result->fetch_assoc()): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <div class="comment-avatar">
                                        <?php
                                        
                                            $nameParts = explode(' ', $comment['username']);
                                            $initials = '';
                                            foreach ($nameParts as $part) {
                                                if (!empty($part)) {
                                                    $initials .= strtoupper(substr($part, 0, 1));
                                                }
                                            }
                                            $initials = substr($initials, 0, 2);
                                            echo htmlspecialchars($initials);
                                        ?>
                                    </div>
                                    <div>
                                        <span class="comment-user"><?= htmlspecialchars($comment['username']) ?></span>
                                        <span class="comment-date"><?= date('M d, Y h:i A', strtotime($comment['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="comment-body">
                                    <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <p>No comments yet. Be the first to comment!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-content" id="quizzes-tab">
            <?php if ($course['creator_id'] === $user_id || $user_role === 'admin'): ?>
                <div style="margin-bottom: 20px;">
                    <button class="admin-btn add-quiz-btn" onclick="location.href='add-quiz.php?course_id=<?= $course['id'] ?>'">Add New Quiz</button>
                </div>
            <?php endif; ?>

            <?php if ($quizzes_result->num_rows > 0): ?>
                <?php while ($quiz = $quizzes_result->fetch_assoc()): ?>
                    <div class="quiz-card">
                        <h3 class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></h3>
                        <p class="quiz-description"><?= htmlspecialchars($quiz['description']) ?></p>
                        <button class="start-quiz-btn" onclick="location.href='take-quiz.php?id=<?= $quiz['id'] ?>'">Start Quiz</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Quizzes Available</h3>
                    <p>This course doesn't have any quizzes yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all content tabs
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Show content for active tab
                    const tabId = this.getAttribute('data-tab') + '-tab';
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });

        function goBack() {
            window.history.back();
        }

        function closeAlert(alertId) {
            document.getElementById(alertId).style.display = 'none';
        }
    </script>
</body>
</html>
