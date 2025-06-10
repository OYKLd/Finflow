<?php
require_once '../config/config.php';

// Fonction d'inscription
function register($email, $password, $name, $currency) {
    global $pdo;

    // Vérification de l'existence de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // L'utilisateur existe déjà
    }

    // Inscription
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, name, currency) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $hashed_password, $name, $currency]);
    return true;
}

// Fonction de connexion
function login($email, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}
?>