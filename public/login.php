<?php
session_start();
require_once '../includes/header.php';
require_once '../functions/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Identifiants invalides!";
    }
}
?>

<div class="container">
    <h2>Connexion</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label for="email">Email</label>
        <input type="email" name="email" required>
        <label for="password">Mot de passe</label>
        <input type="password" name="password" required>
        <button type="submit">Se connecter</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>