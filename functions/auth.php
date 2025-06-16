<?php
require_once '../config/config.php';

// Fonction d'inscription
function register($email, $password, $name, $currency) {
    global $pdo;

    $email = htmlspecialchars($email);
    $name = htmlspecialchars($name);
    $currency = htmlspecialchars($currency);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password, name, currency) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Erreur de préparation de la requête : " . $pdo->errorInfo()[2]);
        return false;
    }

    $stmt->execute([$email, $hashedPassword, $name, $currency]);

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