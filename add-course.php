<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];

    move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);

    $sql = "INSERT INTO courses (title, description, image) VALUES ('$title', '$description', '$image')";
    $conn->query($sql);

    header("Location: index.php");
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Course Title" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <input type="file" name="image" required>
    <button type="submit">Add Course</button>
</form>
