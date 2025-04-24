<?php
session_start();
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: users.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php"); // Redirige vers une page non-admin
    exit();
}
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Veuillez remplir tous les champs.";
        header("Location: index.html");
        exit();
    }
    
    // Vérifier si la table users a une colonne role, sinon l'ajouter
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            
            header("Location: admin.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
        }
    } else {
        $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
    }
    
    $stmt->close();
    $conn->close();
    
    // Rediriger vers la page de connexion avec un message d'erreur
    header("Location: index.html");
    exit();
}
?>