<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT name, email, currency FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found!";
    exit;
}

$name = $user['name'];
$email = $user['email'];
$currency = $user['currency'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $newName = $_POST['name'] ?? '';
        $newEmail = $_POST['email'] ?? '';
        $newCurrency = $_POST['currency'] ?? '';

        // Validate data (add more validation as needed)
        if (empty($newName) || empty($newEmail)) {
            $error = "Name and email are required.";
        } else {
            // Update user data
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, currency = :currency WHERE id = :user_id");
            if ($stmt->execute(['name' => $newName, 'email' => $newEmail, 'currency' => $newCurrency, 'user_id' => $userId])) {
                $success = "Profile updated successfully!";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT name, email, currency FROM users WHERE id = :user_id");
                $stmt->execute(['user_id' => $userId]);
                $user = $stmt->fetch();

                $name = $user['name'];
                $email = $user['email'];
                $currency = $user['currency'];
            } else {
                $error = "Error updating profile.";
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Handle password update
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All password fields are required.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New password and confirm password do not match.";
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
                    $success = "Password updated successfully!";
                } else {
                    $error = "Error updating password.";
                }
            } else {
                $error = "Incorrect old password.";
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
            $error = "Error deleting account.";
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Profile Settings</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Profile Update Form -->
        <h3>Update Profile</h3>
        <form method="POST">
            <input type="hidden" name="update_profile">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="currency">Currency:</label>
                <select name="currency" id="currency">
                    <option value="EUR" <?= $currency === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                    <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                    <option value="XOF" <?= $currency === 'XOF' ? 'selected' : '' ?>>XOF (FCFA)</option>
                </select>
            </div>
            <button type="submit">Update Profile</button>
        </form>

        <!-- Password Update Form -->
        <h3>Update Password</h3>
        <form method="POST">
            <input type="hidden" name="update_password">
            <div class="form-group">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Update Password</button>
        </form>

        <!-- Account Deletion Form -->
        <h3>Delete Account</h3>
        <form method="POST">
            <input type="hidden" name="delete_account">
            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            <button type="submit" style="background-color: #d9534f;">Delete Account</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>