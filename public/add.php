<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Corrected path to config.php
require_once __DIR__ . '/../config/config.php';

$userId = $_SESSION['user_id'];

$type = $_POST['type'] ?? '';
$amount = $_POST['amount'] ?? '';
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($type)) {
        $errors['type'] = 'Le type est obligatoire.';
    }
    if (empty($amount)) {
        $errors['amount'] = 'Le montant est obligatoire.';
    } elseif (!is_numeric($amount)) {
        $errors['amount'] = 'Le montant doit être un nombre.';
    }
    if (empty($description)) {
        $errors['description'] = 'La description est obligatoire.';
    }
    if (empty($category)) {
        $errors['category'] = 'La catégorie est obligatoire.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, category, date) VALUES (:user_id, :type, :amount, :description, :category, :date)");
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'category' => $category,
            'date' => $date
        ]);

        header('Location: dashboard.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une transaction</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h1>Ajouter une transaction</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select class="form-select" id="type" name="type">
                <option value="">Sélectionner...</option>
                <option value="revenu" <?= $type === 'revenu' ? 'selected' : '' ?>>Revenu</option>
                <option value="depense" <?= $type === 'depense' ? 'selected' : '' ?>>Dépense</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Montant</label>
            <input type="number" class="form-control" id="amount" name="amount" value="<?= htmlspecialchars($amount) ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($description) ?>">
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Catégorie</label>
            <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($category) ?>">
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>