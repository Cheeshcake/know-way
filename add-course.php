<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim(htmlspecialchars($_POST['title']));
    $description = trim(htmlspecialchars($_POST['description']));
    
    $target_dir = "uploads/";
    
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
    $stmt = $conn->prepare("INSERT INTO courses (title, description, image, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $title, $description, $image);
    
    if ($stmt->execute()) {
        header("Location: admin.php?success=Course added successfully");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    header("Location: admin.php");
    exit;
}

$conn->close();
?>

