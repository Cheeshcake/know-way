<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $conn->query("DELETE FROM courses WHERE id = $id");
}

header("Location: index.php");
?>