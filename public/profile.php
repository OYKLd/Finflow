<?php
// filepath: c:\xampp\htdocs\Finflow\public\profile.php
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
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        /* Basic styling - can be customized to fit your theme */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: -2;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 80%;
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 50%;
            animation-delay: 10s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            left: 20%;
            animation-delay: 15s;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Header */
        .navbar {
            background: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #667eea !important;
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            background: transparent;
            min-height: 20vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            animation: slideInUp 1s ease-out;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.8);
            animation: slideInUp 1s ease-out 0.2s both;
        }

        .cta-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 18px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            animation: slideInUp 1s ease-out 0.4s both;
        }

        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Features Section */
        .features {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(20px);
            padding: 50px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px 15px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-bottom: 15px;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(102, 126, 234, 0.5);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.5rem;
        }

        /* Testimonials */
        .testimonials {
            background: transparent;
            padding: 50px 0;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px;
            margin: 10px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
        }

        .testimonial-text {
            font-size: 1rem;
            font-style: italic;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
        }

        .testimonial-author {
            font-weight: 600;
            color: #667eea;
        }

        /* Footer */
        footer {
            background: rgba(10, 10, 10, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .cta-button {
                padding: 12px 25px;
                font-size: 0.9rem;
            }
        }

        /* Profile Specific Styles */
        .profile-container {
            margin-top: 5rem; /* Adjust to accommodate fixed navbar */
            padding: 20px;
        }

        .profile-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
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
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, 0.8);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            box-sizing: border-box;
            background: transparent;
            color: #ffffff;
        }

        button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .error {
            color: #ff6666;
        }

        .success {
            color: #66ff66;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg"></div>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-chart-line me-2"></i>Finflow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transaction.php">Transaction</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">À propos</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <div class="container profile-container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2 class="mb-4" style="color: #ffffff;">Profile</h2>
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Avatar Section -->
                <section class="profile-section animate-on-scroll">
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
                </section>

                <!-- Profile Update Section -->
                <section class="profile-section animate-on-scroll">
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
                </section>

                <!-- Password Update Section -->
                <section class="profile-section animate-on-scroll">
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
                </section>

                <!-- Account Deletion Section -->
                <section class="profile-section animate-on-scroll">
                    <h3>Supprimer le compte</h3>
                    <form method="POST">
                        <input type="hidden" name="delete_account">
                        <p style="color: rgba(255, 255, 255, 0.8);">Êtes-vous sûr de vouloir supprimer votre compte? Cette action ne peut pas être annulée.</p>
                        <button type="submit" style="background-color: #d9534f;">Supprimer le compte</button>
                    </form>
                </section>

                <div class="text-center mt-3">
                    <a href="dashboard.php" style="color: #667eea; text-decoration: none;">Retour au tableau de bord</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">&copy; 2025 Finflow - Votre partenaire financier de confiance</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(10, 10, 10, 0.98)';
            } else {
                navbar.style.background = 'rgba(10, 10, 10, 0.95)';
            }
        });

        // Add floating animation to hero visual
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>