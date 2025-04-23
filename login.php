<?php
session_start();
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
        // Vous pouvez définir un utilisateur comme admin ici si nécessaire
        // $conn->query("UPDATE users SET role = 'admin' WHERE email = 'admin@example.com'");
    }
    
    // Préparer la requête pour récupérer l'utilisateur
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Vérifier le mot de passe
        if (password_verify($password, $user['password'])) {
            // Mot de passe correct, créer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            
            // Rediriger vers le tableau de bord
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