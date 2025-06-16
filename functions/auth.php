<?php
require_once __DIR__ . '/../config/config.php';

function register($email, $password, $name, $currency) {
    global $pdo;

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name, currency) VALUES (:email, :password, :name, :currency)");
        $stmt->execute(['email' => $email, 'password' => $hashedPassword, 'name' => $name, 'currency' => $currency]);
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

function login($email, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        return $user['id'];
    }

    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>