<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../view/index.html');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_course'])) {
    $creator_id = $_SESSION['user_id'];
    $title = trim(htmlspecialchars($_POST['title'] ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $video_type = $_POST['video_type'] ?? null;
    $video = null;
    
    // Set default status based on user role
    $status = 'pending';
    
    // If admin is creating course, automatically publish it
    if ($_SESSION['role'] === 'admin') {
        $status = 'published';
    }
    
    // Validate required fields
    if (empty($title) || empty($description)) {
        $error = "Course title and description are required.";
        header("Location: ../view/create-course.php?error=" . urlencode($error));
        exit;
    }
    
    // Check if we're editing an existing course
    $editing = isset($_POST['course_id']) && !empty($_POST['course_id']);
    $course_id = $editing ? $_POST['course_id'] : null;
    
    // If editing, verify the user has permission
    if ($editing) {
        $verify_sql = "SELECT creator_id FROM courses WHERE id = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("i", $course_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            header("Location: ../view/dashboard.php?error=" . urlencode("Course not found"));
            exit;
        }
        
        $course_owner = $verify_result->fetch_assoc()['creator_id'];
        
        if ($course_owner != $creator_id && $_SESSION['role'] !== 'admin') {
            header("Location: ../view/dashboard.php?error=" . urlencode("You don't have permission to edit this course"));
            exit;
        }
    }
    
    // Process image upload if provided
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../view/uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $allowed_image_types = ["jpg", "jpeg", "png", "gif"];
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_image_types)) {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for images.";
            header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
            exit;
        }
        
        if ($_FILES["image"]["size"] > 2000000) { // 2MB limit
            $error = "Sorry, your image file is too large. Maximum size is 2MB.";
            header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
            exit;
        }
        
        $image_name = "course_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
        $target_file = $target_dir . $image_name;
        
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $error = "Sorry, there was an error uploading your image file.";
            header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
            exit;
        }
    }
    
    // Process video based on type
    if ($video_type) {
        switch ($video_type) {
            case 'youtube':
                $youtube_url = $_POST['youtube_url'] ?? '';
                if (!empty($youtube_url)) {
                    $video = $youtube_url;
                }
                break;
                
            case 'vimeo':
                $vimeo_url = $_POST['vimeo_url'] ?? '';
                if (!empty($vimeo_url)) {
                    $video = $vimeo_url;
                }
                break;
                
            case 'uploaded':
                if (isset($_FILES['uploaded_video']) && $_FILES['uploaded_video']['error'] == 0) {
                    $video_dir = "../view/uploads/videos/";
                    
                    if (!file_exists($video_dir)) {
                        mkdir($video_dir, 0777, true);
                    }
                    
                    $allowed_video_types = ["mp4", "webm"];
                    $video_extension = strtolower(pathinfo($_FILES["uploaded_video"]["name"], PATHINFO_EXTENSION));
                    
                    if (!in_array($video_extension, $allowed_video_types)) {
                        $error = "Sorry, only MP4 and WebM video formats are allowed.";
                        header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
                        exit;
                    }
                    
                    if ($_FILES["uploaded_video"]["size"] > 104857600) { // 100MB limit
                        $error = "Sorry, your video file is too large. Maximum size is 100MB.";
                        header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
                        exit;
                    }
                    
                    $video_name = "video_" . time() . "_" . rand(1000, 9999) . "." . $video_extension;
                    $video_target_file = $video_dir . $video_name;
                    
                    if (!move_uploaded_file($_FILES["uploaded_video"]["tmp_name"], $video_target_file)) {
                        $error = "Sorry, there was an error uploading your video file.";
                        header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
                        exit;
                    }
                    
                    $video = $video_name;
                }
                break;
        }
    }
    
    // Database operations inside a transaction
    $conn->begin_transaction();
    
    try {
        if ($editing) {
            // Update existing course
            $update_sql = "UPDATE courses SET 
                           title = ?,
                           description = ?";
            
            $params = [$title, $description];
            $types = "ss";
            
            // Only update image if a new one was uploaded
            if ($image_name) {
                $update_sql .= ", image = ?";
                $params[] = $image_name;
                $types .= "s";
            }
            
            // Update video fields if provided
            if ($video_type) {
                $update_sql .= ", video_type = ?, video = ?";
                $params[] = $video_type;
                $params[] = $video;
                $types .= "ss";
            } elseif ($video_type === '') {
                // If video type is explicitly set to empty, clear the video
                $update_sql .= ", video_type = NULL, video = NULL";
            }
            
            $update_sql .= " WHERE id = ?";
            $params[] = $course_id;
            $types .= "i";
            
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $success_message = "Course updated successfully!";
        } else {
            // Create new course
            $insert_sql = "INSERT INTO courses (creator_id, title, description, image, video_type, video, status, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("issssss", $creator_id, $title, $description, $image_name, $video_type, $video, $status);
            $stmt->execute();
            
            $course_id = $conn->insert_id;
            $success_message = "Course created successfully!";
        }
        
        $conn->commit();
        
        // If redirect to quiz is requested
        if (isset($_POST['add_quiz_after_save']) && $_POST['add_quiz_after_save'] == '1') {
            header("Location: ../view/add-quiz.php?course_id=$course_id");
            exit;
        }
        
        // Redirect based on user role
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../view/admin.php?success=" . urlencode($success_message));
        } else {
            header("Location: ../view/dashboard.php?success=" . urlencode($success_message));
        }
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error saving course: " . $e->getMessage();
        header("Location: ../view/create-course.php" . ($editing ? "?edit=$course_id&" : "?") . "error=" . urlencode($error));
        exit;
    }
} else {
    // Redirect to appropriate page if accessed directly
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../view/admin.php");
    } else {
        header("Location: ../view/dashboard.php");
    }
    exit;
}

$conn->close();
?> 