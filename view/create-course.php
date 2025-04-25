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

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$error_message = '';
$success_message = '';

// Check for course ID in case of editing
$editing = false;
$course_id = null;
$course = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $course_id = $_GET['edit'];
    $editing = true;
    
    // Fetch course details
    $course_sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $conn->prepare($course_sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Course not found, redirect
        header('Location: dashboard.php?error=' . urlencode('Course not found'));
        exit;
    }
    
    $course = $result->fetch_assoc();
    
    // Check if user is allowed to edit this course
    if ($course['creator_id'] !== $user_id && $user_role !== 'admin') {
        // Not authorized, redirect
        header('Location: dashboard.php?error=' . urlencode('You are not authorized to edit this course'));
        exit;
    }
}

// Process form submission if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    // Form data will be processed by ../controller/save-course.php
}

// Get error/success messages from URL parameters
if (empty($error_message)) {
    $error_message = $_GET['error'] ?? '';
}
if (empty($success_message)) {
    $success_message = $_GET['success'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Edit' : 'Create' ?> Course - KnowWay</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: rgba(79, 70, 229, 0.1);
            --primary-lighter: rgba(79, 70, 229, 0.05);
            --success: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --danger: #ef4444;
            --danger-light: rgba(239, 68, 68, 0.1);
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        body {
            background-color: var(--gray-50);
            font-family: 'Inter', sans-serif;
            color: var(--gray-800);
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .course-form-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--primary);
            padding: 8px 12px;
            font-weight: 500;
            margin-bottom: 24px;
            border-radius: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background-color: var(--primary-lighter);
            transform: translateX(-3px);
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        /* Enhanced Page Header */
        .page-header {
            margin-bottom: 40px;
            position: relative;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--gray-900);
            line-height: 1.2;
            background: linear-gradient(to right, var(--gray-900), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        
        .page-description {
            color: var(--gray-600);
            margin-bottom: 0;
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 700px;
        }
        
        /* Enhanced Form Tabs */
        .form-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--gray-200);
            position: relative;
            background-color: white;
            border-radius: 12px 12px 0 0;
            padding: 0 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .form-tab {
            padding: 18px 24px;
            cursor: pointer;
            color: var(--gray-500);
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        
        .form-tab:hover {
            color: var(--primary);
            background-color: var(--primary-lighter);
        }
        
        .form-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .form-tab-icon {
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: transform 0.3s;
        }
        
        .form-tab:hover .form-tab-icon {
            transform: scale(1.1);
        }
        
        .info-icon {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>');
        }
        
        .video-icon {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>');
        }
        
        .quiz-icon {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>');
        }
        
        /* Enhanced Tab Content */
        .form-tab-content {
            display: none;
            animation: fadeIn 0.5s;
            width: 100%;
        }
        
        .form-tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Enhanced Form Sections */
        .form-section {
            width: 100%;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid var(--gray-100);
        }
        
        .form-section:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .form-section-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 25px;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .form-section-title::before {
            content: '';
            display: block;
            width: 24px;
            height: 24px;
            background-color: var(--primary-light);
            border-radius: 50%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>');
            background-size: 14px;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* Enhanced Form Controls */
        .form-group {
            margin-bottom: 28px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            transition: all 0.3s;
            color: var(--gray-800);
            background-color: var(--gray-50);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            background-color: white;
        }
        
        .form-control::placeholder {
            color: var(--gray-400);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 1.6;
        }
        
        .form-hint {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--gray-500);
            line-height: 1.5;
        }
        
        .form-hint a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .form-hint a:hover {
            text-decoration: underline;
        }
        
        /* Enhanced Image Upload */
        .image-preview-container {
            margin-top: 16px;
            border: 2px dashed var(--gray-300);
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            transition: all 0.3s;
            background-color: var(--gray-50);
        }
        
        .image-preview-container:hover {
            border-color: var(--primary);
            background-color: var(--primary-lighter);
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 250px;
            margin: 15px auto 0;
            border-radius: 8px;
            display: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .image-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 10px;
            font-weight: 500;
            color: var(--gray-600);
        }
        
        .file-input {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .upload-icon {
            display: inline-block;
            width: 50px;
            height: 50px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        
        .image-preview-container:hover .upload-icon {
            transform: translateY(-5px);
        }
        
        /* Enhanced Video Options */
        .video-option-container {
            margin-bottom: 30px;
            background-color: var(--gray-50);
            padding: 20px;
            border-radius: 10px;
        }
        
        .video-option {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            cursor: pointer;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        
        .video-option:hover {
            background-color: white;
            border-color: var(--gray-200);
            transform: translateX(5px);
        }
        
        .video-option input[type="radio"] {
            margin-right: 12px;
            cursor: pointer;
            accent-color: var(--primary);
            width: 20px;
            height: 20px;
        }
        
        .video-option label {
            font-weight: 500;
            cursor: pointer;
            font-size: 1rem;
            color: var(--gray-700);
        }
        
        .video-input-container {
            margin-top: 25px;
            display: none;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .video-input-container.active {
            display: block;
            animation: fadeIn 0.4s;
        }
        
        .video-preview {
            margin-top: 25px;
            display: none;
            text-align: center;
            background-color: var(--gray-50);
            padding: 20px;
            border-radius: 10px;
        }
        
        .video-preview iframe {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            aspect-ratio: 16/9;
            width: 100%;
            max-width: 640px;
        }
        
        /* Enhanced Course Preview Card */
        .preview-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
            transition: all 0.4s;
            transform: perspective(1000px) rotateY(0deg);
            transform-style: preserve-3d;
        }
        
        .preview-card:hover {
            transform: perspective(1000px) rotateY(5deg);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .preview-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .preview-card:hover .preview-image {
            transform: scale(1.05);
        }
        
        .preview-content {
            padding: 25px;
        }
        
        .preview-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--gray-800);
            line-height: 1.3;
        }
        
        .preview-description {
            font-size: 1rem;
            color: var(--gray-600);
            margin-bottom: 20px;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
        }
        
        .preview-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--gray-500);
            font-size: 0.9rem;
            padding-top: 15px;
            border-top: 1px solid var(--gray-100);
        }
        
        .preview-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .preview-meta span::before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* Enhanced Quiz List */
        .quiz-list-empty {
            text-align: center;
            padding: 50px 30px;
            background-color: var(--gray-50);
            border-radius: 12px;
            border: 2px dashed var(--gray-300);
            color: var(--gray-500);
            margin: 20px 0;
        }
        
        .quiz-list-empty::before {
            content: '';
            display: block;
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%239ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .quiz-list {
            margin-top: 20px;
        }
        
        .quiz-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s;
            background-color: white;
        }
        
        .quiz-list-item:hover {
            border-color: var(--primary);
            background-color: var(--primary-lighter);
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .quiz-list-title {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quiz-list-title::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .quiz-list-actions {
            display: flex;
            gap: 10px;
        }
        
        .quiz-list-btn {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .quiz-list-btn-edit {
            background-color: var(--gray-100);
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
        }
        
        .quiz-list-btn-edit::before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .quiz-list-btn-edit:hover {
            background-color: var(--gray-200);
            color: var(--gray-800);
            transform: translateY(-2px);
        }
        
        .quiz-list-btn-delete {
            background-color: var(--danger-light);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .quiz-list-btn-delete::before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .quiz-list-btn-delete:hover {
            background-color: var(--danger);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Enhanced Navigation and Actions */
        .tab-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }
        
        .btn {
            border-radius: 6px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: fit-content;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: white;
            background-color: var(--primary);
            border: 1px solid var(--gray-200);
            
        }
        
        .btn-outline:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
            border: none;
        }
        
        .btn-success:hover {
            background-color: #0ca678;
        }
        
        .quiz-icon-small {
            width: 16px;
            height: 16px;
            display: inline-block;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* Progress Indicator */
        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        
        .progress-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--gray-200);
            transform: translateY(-50%);
            z-index: 0;
        }
        
        .progress-step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            border: 2px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray-500);
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }
        
        .progress-step.active {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 0 0 5px var(--primary-light);
        }
        
        .progress-step.completed {
            background-color: var(--success);
            border-color: var(--success);
            color: white;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .course-form-container {
                padding: 20px 15px;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .form-tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
                padding: 0 10px;
            }
            
            .form-tab {
                padding: 15px;
                font-size: 0.9rem;
            }
            
            .video-preview iframe {
                height: 200px;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                max-width: 100%;
                justify-content: center;
                padding: 12px 20px;
            }
            
            .tab-navigation {
                flex-direction: column;
                gap: 15px;
            }
            
            .tab-navigation .btn {
                width: 100%;
                justify-content: center;
            }
            
            .preview-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="course-form-container">
        <a class="back-btn" href="<?= $user_role === 'admin' ? 'admin.php' : 'dashboard.php' ?>">
            <span class="back-icon"></span>
            Back to <?= $user_role === 'admin' ? 'Admin Dashboard' : 'Dashboard' ?>
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
        
        <div class="page-header">
            <h1 class="page-title"><?= $editing ? 'Edit' : 'Create New' ?> Course</h1>
            <p class="page-description">Share your knowledge with the world. Complete all the required information below to <?= $editing ? 'update your' : 'create a new' ?> course.</p>
        </div>
        
        <div class="progress-indicator">
            <div class="progress-step active" data-step="1">1</div>
            <div class="progress-step" data-step="2">2</div>
            <div class="progress-step" data-step="3">3</div>
        </div>
        
        <div class="form-tabs">
            <div class="form-tab active" data-tab="basic-info">
                <span class="form-tab-icon info-icon"></span>
                Basic Information
            </div>
            <div class="form-tab" data-tab="course-video">
                <span class="form-tab-icon video-icon"></span>
                Course Video
            </div>
            <div class="form-tab" data-tab="course-quiz">
                <span class="form-tab-icon quiz-icon"></span>
                Quiz
            </div>
        </div>
        
        <form method="POST" action="../controller/save-course.php" enctype="multipart/form-data" id="courseForm" style="width: 100%; max-width: 1200px; ">
            <?php if ($editing): ?>
                <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <?php endif; ?>
            <input type="hidden" name="save_course" value="1">

            <!-- Basic Information Tab -->
            <div class="form-tab-content active" id="basic-info-tab">
                <div class="form-section">
                    <h3 class="form-section-title">Course Details</h3>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Enter course title" value="<?= $editing ? htmlspecialchars($course['title']) : '' ?>" required>
                        <div class="form-hint">Choose a descriptive title that clearly communicates what the course is about.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Course Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Enter detailed course description" required><?= $editing ? htmlspecialchars($course['description']) : '' ?></textarea>
                        <div class="form-hint">Describe what students will learn, prerequisites, and any other relevant details.</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Course Image</label>
                        <div class="image-preview-container">
                            <label class="image-upload-label">
                                <span class="upload-icon"></span>
                                <span id="image-label-text"><?= $editing && !empty($course['image']) ? 'Change image' : 'Upload an image' ?></span>
                                <input type="file" id="image-upload" name="image" class="file-input" <?= $editing ? '' : 'required' ?> accept="image/*">
                            </label>
                            <img id="image-preview" src="<?= $editing && !empty($course['image']) ? 'uploads/' . htmlspecialchars($course['image']) : '' ?>" class="image-preview" style="<?= $editing && !empty($course['image']) ? 'display: block;' : '' ?>">
                        </div>
                        <div class="form-hint">Recommended size: 1280×720 pixels (16:9 ratio). Max file size: 2MB.</div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">Course Preview</h3>
                    <div class="preview-card">
                        <img id="preview-image" src="<?= $editing && !empty($course['image']) ? 'uploads/' . htmlspecialchars($course['image']) : 'placeholder-course.png' ?>" class="preview-image">
                        <div class="preview-content">
                            <h4 id="preview-title" class="preview-title"><?= $editing ? htmlspecialchars($course['title']) : 'Your Course Title' ?></h4>
                            <p id="preview-description" class="preview-description"><?= $editing ? htmlspecialchars($course['description']) : 'Your course description will appear here.' ?></p>
                            <div class="preview-meta">
                                <span>Created by: <?= htmlspecialchars($_SESSION['username']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-navigation">
                    <div></div>
                    <button type="button" class="btn btn-primary next-tab" data-next="course-video">
                        Next
                    </button>
                </div>
            </div>
            
            <!-- Course Video Tab -->
            <div class="form-tab-content" id="course-video-tab">
                <div class="form-section">
                    <h3 class="form-section-title">Course Video</h3>
                    
                    <div class="video-option-container">
                        <p class="form-label">Select Video Source</p>
                        
                        <div class="video-option">
                            <input type="radio" id="video-youtube" name="video_type" value="youtube" <?= $editing && $course['video_type'] === 'youtube' ? 'checked' : '' ?>>
                            <label for="video-youtube">YouTube Video</label>
                        </div>
                        
                        <div class="video-option">
                            <input type="radio" id="video-vimeo" name="video_type" value="vimeo" <?= $editing && $course['video_type'] === 'vimeo' ? 'checked' : '' ?>>
                            <label for="video-vimeo">Vimeo Video</label>
                        </div>
                        
                        <div class="video-option">
                            <input type="radio" id="video-upload" name="video_type" value="uploaded" <?= $editing && $course['video_type'] === 'uploaded' ? 'checked' : '' ?>>
                            <label for="video-upload">Upload Video</label>
                        </div>
                    </div>
                    
                    <!-- YouTube Input -->
                    <div id="youtube-input" class="video-input-container">
                        <div class="form-group">
                            <label for="youtube-url" class="form-label">YouTube URL</label>
                            <input type="text" id="youtube-url" name="youtube_url" class="form-control" placeholder="e.g., https://www.youtube.com/watch?v=XXXXXXXXXXX" value="<?= $editing && $course['video_type'] === 'youtube' ? htmlspecialchars($course['video']) : '' ?>">
                            <div class="form-hint">Paste the YouTube URL of your video.</div>
                        </div>
                        
                        <div id="youtube-preview" class="video-preview" style="<?= $editing && $course['video_type'] === 'youtube' && !empty($course['video']) ? 'display: block;' : '' ?>"></div>
                    </div>
                    
                    <!-- Vimeo Input -->
                    <div id="vimeo-input" class="video-input-container">
                        <div class="form-group">
                            <label for="vimeo-url" class="form-label">Vimeo URL</label>
                            <input type="text" id="vimeo-url" name="vimeo_url" class="form-control" placeholder="e.g., https://vimeo.com/XXXXXXXXX" value="<?= $editing && $course['video_type'] === 'vimeo' ? htmlspecialchars($course['video']) : '' ?>">
                            <div class="form-hint">Paste the Vimeo URL of your video.</div>
                        </div>
                        
                        <div id="vimeo-preview" class="video-preview" style="<?= $editing && $course['video_type'] === 'vimeo' && !empty($course['video']) ? 'display: block;' : '' ?>"></div>
                    </div>
                    
                    <!-- Upload Video Input -->
                    <div id="uploaded-input" class="video-input-container">
                        <div class="form-group">
                            <label class="form-label">Upload Video File</label>
                            <div class="image-preview-container">
                                <label class="image-upload-label">
                                    <span class="upload-icon"></span>
                                    <span id="video-label-text"><?= $editing && $course['video_type'] === 'uploaded' ? 'Change video' : 'Upload your video' ?></span>
                                    <input type="file" id="video-upload-input" name="uploaded_video" class="file-input" accept="video/mp4,video/webm">
                                </label>
                            </div>
                            <div class="form-hint">Accepted formats: MP4, WebM. Maximum file size: 100MB.</div>
                        </div>
                        
                        <div id="upload-preview" class="video-preview"></div>
                    </div>
                </div>
                
                <div class="tab-navigation">
                    <button type="button" class="btn btn-outline prev-tab" data-prev="basic-info">
                        Previous
                    </button>
                    <button type="button" class="btn btn-primary next-tab" data-next="course-quiz">
                        Next
                    </button>
                </div>
            </div>
            
            <!-- Quiz Tab -->
            <div class="form-tab-content" id="course-quiz-tab">
                <div class="form-section">
                    <h3 class="form-section-title">Course Quiz</h3>
                    
                    <?php if ($editing): ?>
                        <?php
                        // Check if there are existing quizzes for this course
                        $quiz_sql = "SELECT id, title FROM course_quizzes WHERE course_id = ?";
                        $quiz_stmt = $conn->prepare($quiz_sql);
                        $quiz_stmt->bind_param("i", $course_id);
                        $quiz_stmt->execute();
                        $quiz_result = $quiz_stmt->get_result();
                        ?>
                        
                        <?php if ($quiz_result->num_rows > 0): ?>
                            <p>This course has the following quizzes:</p>
                            <div class="quiz-list">
                                <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
                                    <div class="quiz-list-item">
                                        <div class="quiz-list-title"><?= htmlspecialchars($quiz['title']) ?></div>
                                        <div class="quiz-list-actions">
                                            <a href="edit-quiz.php?id=<?= $quiz['id'] ?>" class="quiz-list-btn quiz-list-btn-edit">Edit</a>
                                            <button type="button" class="quiz-list-btn quiz-list-btn-delete" onclick="if(confirm('Are you sure you want to delete this quiz?')) window.location.href='../controller/delete-quiz.php?id=<?= $quiz['id'] ?>&course_id=<?= $course_id ?>'">Delete</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="quiz-list-empty">
                                <p>No quizzes have been added to this course yet.</p>
                                <p>You can add a quiz after saving the course.</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="quiz-list-empty">
                            <p>You can add a quiz after saving your course.</p>
                            <p>First complete the course details and save.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline prev-tab" data-prev="course-video">
                        Previous
                    </button>
                    <div style="display: flex; gap: 10px;  align-items: center;">
                        <button type="submit" class="btn btn-primary">
                            <?= $editing ? 'Update Course' : 'Create Course' ?>
                        </button>
                        <?php if ($editing): ?>
                            <button type="button" class="btn btn-success" onclick="saveAndAddQuiz()">
                                <span class="quiz-icon-small"></span> Manage Quiz
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success" onclick="saveAndAddQuiz()">
                                <span class="quiz-icon-small"></span> Save & Add Quiz
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Navigation
            const tabs = document.querySelectorAll('.form-tab');
            const tabContents = document.querySelectorAll('.form-tab-content');
            const nextButtons = document.querySelectorAll('.next-tab');
            const prevButtons = document.querySelectorAll('.prev-tab');
            const progressSteps = document.querySelectorAll('.progress-step');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetTab = tab.getAttribute('data-tab');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    // Update active content
                    tabContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(targetTab + '-tab').classList.add('active');
                    
                    // Update progress steps
                    updateProgressSteps(targetTab);
                });
            });
            
            nextButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const nextTab = button.getAttribute('data-next');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    document.querySelector(`[data-tab="${nextTab}"]`).classList.add('active');
                    
                    // Update active content
                    tabContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(nextTab + '-tab').classList.add('active');
                    
                    // Update progress steps
                    updateProgressSteps(nextTab);
                });
            });
            
            prevButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const prevTab = button.getAttribute('data-prev');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    document.querySelector(`[data-tab="${prevTab}"]`).classList.add('active');
                    
                    // Update active content
                    tabContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(prevTab + '-tab').classList.add('active');
                    
                    // Update progress steps
                    updateProgressSteps(prevTab);
                });
            });
            
            function updateProgressSteps(activeTab) {
                progressSteps.forEach(step => step.classList.remove('active'));
                
                if (activeTab === 'basic-info') {
                    progressSteps[0].classList.add('active');
                } else if (activeTab === 'course-video') {
                    progressSteps[1].classList.add('active');
                    progressSteps[0].classList.add('completed');
                } else if (activeTab === 'course-quiz') {
                    progressSteps[2].classList.add('active');
                    progressSteps[0].classList.add('completed');
                    progressSteps[1].classList.add('completed');
                }
            }
            
            // Course Image Upload Preview
            const imageUpload = document.getElementById('image-upload');
            const imagePreview = document.getElementById('image-preview');
            const imageLabelText = document.getElementById('image-label-text');
            const previewImage = document.getElementById('preview-image');
            
            if (imageUpload) {
                imageUpload.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            imagePreview.style.display = 'block';
                            previewImage.src = e.target.result;
                            imageLabelText.textContent = 'Change image';
                        }
                        
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Update preview card as user types
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const previewTitle = document.getElementById('preview-title');
            const previewDescription = document.getElementById('preview-description');
            
            if (titleInput) {
                titleInput.addEventListener('input', function() {
                    previewTitle.textContent = this.value || 'Your Course Title';
                });
            }
            
            if (descriptionInput) {
                descriptionInput.addEventListener('input', function() {
                    previewDescription.textContent = this.value || 'Your course description will appear here.';
                });
            }
            
            // Video Type Toggle
            const videoTypeRadios = document.querySelectorAll('input[name="video_type"]');
            const videoContainers = document.querySelectorAll('.video-input-container');
            
            videoTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    videoContainers.forEach(container => {
                        container.classList.remove('active');
                        container.style.display = 'none';
                    });
                    
                    const selectedType = this.value;
                    if (selectedType === 'youtube') {
                        document.getElementById('youtube-input').classList.add('active');
                        document.getElementById('youtube-input').style.display = 'block';
                    } else if (selectedType === 'vimeo') {
                        document.getElementById('vimeo-input').classList.add('active');
                        document.getElementById('vimeo-input').style.display = 'block';
                    } else if (selectedType === 'uploaded') {
                        document.getElementById('uploaded-input').classList.add('active');
                        document.getElementById('uploaded-input').style.display = 'block';
                    }
                });
            });
            
            // Initialize video container visibility
            let videoTypeSelected = false;
            videoTypeRadios.forEach(radio => {
                if (radio.checked) {
                    videoTypeSelected = true;
                    const selectedType = radio.value;
                    if (selectedType === 'youtube') {
                        document.getElementById('youtube-input').classList.add('active');
                        document.getElementById('youtube-input').style.display = 'block';
                    } else if (selectedType === 'vimeo') {
                        document.getElementById('vimeo-input').classList.add('active');
                        document.getElementById('vimeo-input').style.display = 'block';
                    } else if (selectedType === 'uploaded') {
                        document.getElementById('uploaded-input').classList.add('active');
                        document.getElementById('uploaded-input').style.display = 'block';
                    }
                }
            });
            
            if (!videoTypeSelected) {
                videoContainers.forEach(container => {
                    container.style.display = 'none';
                });
            }
            
            // YouTube Preview
            const youtubeUrl = document.getElementById('youtube-url');
            const youtubePreview = document.getElementById('youtube-preview');
            
            if (youtubeUrl) {
                youtubeUrl.addEventListener('change', function() {
                    const url = this.value;
                    if (url) {
                        const videoId = getYouTubeVideoId(url);
                        if (videoId) {
                            youtubePreview.innerHTML = `<iframe width="560" height="315" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                            youtubePreview.style.display = 'block';
                        } else {
                            youtubePreview.innerHTML = '<p class="error-message">Invalid YouTube URL. Please enter a valid YouTube URL.</p>';
                            youtubePreview.style.display = 'block';
                        }
                    } else {
                        youtubePreview.innerHTML = '';
                        youtubePreview.style.display = 'none';
                    }
                });
                
                // Trigger change event if URL is pre-filled
                if (youtubeUrl.value) {
                    const event = new Event('change');
                    youtubeUrl.dispatchEvent(event);
                }
            }
            
            // Vimeo Preview
            const vimeoUrl = document.getElementById('vimeo-url');
            const vimeoPreview = document.getElementById('vimeo-preview');
            
            if (vimeoUrl) {
                vimeoUrl.addEventListener('change', function() {
                    const url = this.value;
                    if (url) {
                        const videoId = getVimeoVideoId(url);
                        if (videoId) {
                            vimeoPreview.innerHTML = `<iframe src="https://player.vimeo.com/video/${videoId}" width="560" height="315" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
                            vimeoPreview.style.display = 'block';
                        } else {
                            vimeoPreview.innerHTML = '<p class="error-message">Invalid Vimeo URL. Please enter a valid Vimeo URL.</p>';
                            vimeoPreview.style.display = 'block';
                        }
                    } else {
                        vimeoPreview.innerHTML = '';
                        vimeoPreview.style.display = 'none';
                    }
                });
                
                // Trigger change event if URL is pre-filled
                if (vimeoUrl.value) {
                    const event = new Event('change');
                    vimeoUrl.dispatchEvent(event);
                }
            }
            
            // Uploaded Video Preview
            const videoUpload = document.getElementById('video-upload-input');
            const uploadPreview = document.getElementById('upload-preview');
            const videoLabelText = document.getElementById('video-label-text');
            
            if (videoUpload) {
                videoUpload.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        uploadPreview.innerHTML = `<video width="560" height="315" controls><source src="${url}" type="${file.type}">Your browser does not support the video tag.</video>`;
                        uploadPreview.style.display = 'block';
                        videoLabelText.textContent = 'Change video';
                    }
                });
            }
            
            // Function to extract YouTube video ID
            function getYouTubeVideoId(url) {
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                const match = url.match(regExp);
                return (match && match[2].length === 11) ? match[2] : null;
            }
            
            // Function to extract Vimeo video ID
            function getVimeoVideoId(url) {
                const regExp = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|)(\d+)(?:$|\/|\?)/;
                const match = url.match(regExp);
                return match ? match[1] : null;
            }

            // Save and Add Quiz function
            window.saveAndAddQuiz = function() {
                // Add hidden field to indicate action
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'add_quiz_after_save';
                hiddenInput.value = '1';
                document.getElementById('courseForm').appendChild(hiddenInput);
                
                // Submit the form
                document.getElementById('courseForm').submit();
            };
            
            // Close alert function
            window.closeAlert = function(alertId) {
                const alert = document.getElementById(alertId);
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-15px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            };
        });
    </script>
</body>
</html>
