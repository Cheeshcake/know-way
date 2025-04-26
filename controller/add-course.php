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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim(htmlspecialchars($_POST['title']));
    $description = trim(htmlspecialchars($_POST['description']));
    $creator_id = $_SESSION['user_id'];
    
    // Set default status based on user role
    $status = 'pending';
    
    // If admin is creating course, automatically publish it
    if ($_SESSION['role'] === 'admin') {
        $status = 'published';
    }
    
    $target_dir = "../view/uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image = "";
    
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        
        if(in_array($file_extension, $allowed_types)) {
            $image = "course_" . time() . "." . $file_extension;
            $target_file = $target_dir . $image;
            
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // would make a toast here for success but they said no frameworks soooo...
            } else {
                echo "Sorry, there was an error uploading your file.";
                exit;
            }
        } else {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            exit;
        }
    } else {
        echo "Please select an image.";
        exit;
    }
    
    // prevent SQL injection (screw you hackers)
    $stmt = $conn->prepare("INSERT INTO courses (creator_id, title, description, image, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $creator_id, $title, $description, $image, $status);
    
    if ($stmt->execute()) {
        // Redirect based on user role
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../view/admin.php?success=Course added successfully");
        } else {
            header("Location: ../view/dashboard.php?success=Course submitted for approval");
        }
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    // Redirect based on user role
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../view/admin.php");
    } else {
        header("Location: ../view/dashboard.php");
    }
    exit;
}

$conn->close();
?>

