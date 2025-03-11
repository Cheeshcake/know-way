<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if($id === false) {
        echo "Invalid course ID";
        exit;
    }
    
    $stmt = $conn->prepare("SELECT image FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        $image = $row['image'];
        
        if(!empty($image)) {
            $image_path = "uploads/" . $image;
            if(file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $delete_stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            header("Location: admin.php?success=Course deleted successfully");
            exit;
        } else {
            echo "Error: " . $delete_stmt->error;
        }
        
        $delete_stmt->close();
    } else {
        echo "Course not found";
    }
    
    $stmt->close();
} else {
    header("Location: admin.php");
    exit;
}

$conn->close();
?>

