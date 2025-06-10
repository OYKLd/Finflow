<?php
require_once '../includes/header.php';
require_once '../functions/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $currency = $_POST['currency'];

    if (register($email, $password, $name, $currency)) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Erreur lors de l'inscription!";
    }
}
?>

<div class="container">
    <h2>Inscription</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label for="name">Nom</label>
        <input type="text" name="name" required>
        <label for="email">Email</label>
        <input type="email" name="email" required>
        <label for="password">Mot de passe</label>
        <input type="password" name="password" required>
        <label for="currency">Devise</label>
        <input type="text" name="currency" required>
        <button type="submit">S'inscrire</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>