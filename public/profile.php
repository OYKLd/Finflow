<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT name, email, currency, avatar FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

$name = $user['name'];
$email = $user['email'];
$currency = $user['currency'];
$avatar = $user['avatar']; // Get the avatar path from the database
$error = '';
$success = '';

// Define the upload directory
$uploadDir = 'uploads/'; // Create this directory in your public folder
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $newName = $_POST['name'] ?? '';
        $newEmail = $_POST['email'] ?? '';
        $newCurrency = $_POST['currency'] ?? '';

        // Validate data (add more validation as needed)
        if (empty($newName) || empty($newEmail)) {
            $error = "Nom et email sont requis.";
        } else {
            // Update user data
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, currency = :currency WHERE id = :user_id");
            if ($stmt->execute(['name' => $newName, 'email' => $newEmail, 'currency' => $newCurrency, 'user_id' => $userId])) {
                $success = "Profil mis à jour avec succès!";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT name, email, currency, avatar FROM users WHERE id = :user_id");
                $stmt->execute(['user_id' => $userId]);
                $user = $stmt->fetch();

                $name = $user['name'];
                $email = $user['email'];
                $currency = $user['currency'];
                $avatar = $user['avatar'];
            } else {
                $error = "Erreur lors de la mise à jour du profil.";
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Handle password update
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "Tous les champs de mot de passe sont requis.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Le nouveau mot de passe et la confirmation du mot de passe ne correspondent pas.";
        } else {
            // Verify old password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $dbUser = $stmt->fetch();

            if ($dbUser && password_verify($oldPassword, $dbUser['password'])) {
                // Hash and update new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                if ($stmt->execute(['password' => $hashedPassword, 'user_id' => $userId])) {
                    $success = "Mot de passe mis à jour avec succès!";
                } else {
                    $error = "Erreur lors de la mise à jour du mot de passe.";
                }
            } else {
                $error = "Ancien mot de passe incorrect.";
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        // Handle account deletion
        // Add confirmation step here for security
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        if ($stmt->execute(['user_id' => $userId])) {
            // Redirect to logout or home page after deletion
            header("Location: logout.php");
            exit;
        } else {
            $error = "Erreur lors de la suppression du compte.";
        }
    } elseif (isset($_POST['upload_avatar'])) {
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileSize = $_FILES['avatar']['size'];
            $fileType = $_FILES['avatar']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Unique file name
            $destFilePath = $uploadDir . $newFileName;

            // Validate file type and size
            $allowedfileTypes = ['jpg', 'png', 'jpeg'];
            if (in_array($fileExtension, $allowedfileTypes)) {
                if ($fileSize < 2000000) { // 2MB
                    if (move_uploaded_file($fileTmpPath, $destFilePath)) {
                        // Update the database with the new avatar path
                        $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :user_id");
                        if ($stmt->execute(['avatar' => $destFilePath, 'user_id' => $userId])) {
                            $success = "Avatar mis à jour avec succès!";
                            // Refresh user data
                            $stmt = $pdo->prepare("SELECT name, email, currency, avatar FROM users WHERE id = :user_id");
                            $stmt->execute(['user_id' => $userId]);
                            $user = $stmt->fetch();

                            $name = $user['name'];
                            $email = $user['email'];
                            $currency = $user['currency'];
                            $avatar = $user['avatar'];
                        } else {
                            $error = "Erreur lors de la mise à jour du chemin de l'avatar dans la base de données.";
                        }
                    } else {
                        $error = "Erreur lors du déplacement du fichier téléchargé.";
                    }
                } else {
                    $error = "La taille du fichier dépasse la limite (2 Mo).";
                }
            } else {
                $error = "Type de fichier invalide. Seuls les fichiers JPG, JPEG et PNG sont autorisés.";
            }
        } else {
            $error = "Aucun fichier téléchargé ou erreur de téléchargement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Finflow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Basic styling - can be customized to fit your theme */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .avatar-preview img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Profile</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Avatar Upload Form -->
        <h3>Avatar</h3>
        <div class="avatar-preview">
            <?php if ($avatar): ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar">
            <?php else: ?>
                <img src="https://via.placeholder.com/100" alt="No Avatar">
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="upload_avatar">
            <div class="form-group">
                <label for="avatar">Choisir un nouvel avatar:</label>
                <input type="file" name="avatar" id="avatar">
            </div>
            <button type="submit">Télécharger l'avatar</button>
        </form>

        <!-- Profile Update Form -->
        <h3>Mettre à jour le profil</h3>
        <form method="POST">
            <input type="hidden" name="update_profile">
            <div class="form-group">
                <label for="name">Nom:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="currency">Devise:</label>
                <select name="currency" id="currency">
                    <option value="EUR" <?= $currency === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                    <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                    <option value="XOF" <?= $currency === 'XOF' ? 'selected' : '' ?>>XOF (FCFA)</option>
                </select>
            </div>
            <button type="submit">Mettre à jour le profil</button>
        </form>

        <!-- Password Update Form -->
        <h3>Mettre à jour le mot de passe</h3>
        <form method="POST">
            <input type="hidden" name="update_password">
            <div class="form-group">
                <label for="old_password">Ancien mot de passe:</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Mettre à jour le mot de passe</button>
        </form>

        <!-- Account Deletion Form -->
        <h3>Supprimer le compte</h3>
        <form method="POST">
            <input type="hidden" name="delete_account">
            <p>Êtes-vous sûr de vouloir supprimer votre compte? Cette action ne peut pas être annulée.</p>
            <button type="submit" style="background-color: #d9534f;">Supprimer le compte</button>
        </form>
        <a href="dashboard.php">Retour au tableau de bord</a>
    </div>
</body>
</html>