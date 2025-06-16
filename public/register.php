<?php
require_once '../functions/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $currency = $_POST['currency'] ?? '';

    if (register($email, $password, $name, $currency)) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Erreur lors de l'inscription!";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Finflow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            min-height: 100vh;
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
            animation: float 15s infinite linear;
        }

        .shape:nth-child(1) {
            width: 60px;
            height: 60px;
            left: 15%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 90px;
            height: 90px;
            left: 85%;
            animation-delay: 3s;
        }

        .shape:nth-child(3) {
            width: 40px;
            height: 40px;
            left: 70%;
            animation-delay: 6s;
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
            padding: 1rem 0;
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
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #667eea !important;
        }

        /* Main Container */
        .registration-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 120px 0 80px;
        }

        .registration-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 50px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            animation: slideInUp 0.8s ease-out;
            max-width: 500px;
            width: 100%;
        }

        .registration-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.4);
        }

        .registration-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .registration-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .registration-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 15px 20px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
            color: black;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            margin-bottom: 20px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #5a6fd8, #6a4c93);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Social Login */
        .social-login {
            margin-top: 30px;
            text-align: center;
        }

        .divider {
            position: relative;
            margin: 30px 0;
            text-align: center;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .divider span {
            background: rgba(10, 10, 10, 0.8);
            padding: 0 20px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .btn-social {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 20px;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-social:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            color: #ffffff;
            text-decoration: none;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
            text-decoration: underline;
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

        /* Responsive */
        @media (max-width: 768px) {
            .registration-card {
                padding: 30px 25px;
                margin: 20px;
            }
            
            .registration-header h1 {
                font-size: 2rem;
            }
            
            .form-control {
                padding: 12px 15px;
            }
        }

        /* Success/Error Messages */
        .alert {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            border-color: rgba(40, 167, 69, 0.5);
            background: rgba(40, 167, 69, 0.1);
        }

        .alert-danger {
            border-color: rgba(220, 53, 69, 0.5);
            background: rgba(220, 53, 69, 0.1);
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
    </div>

    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line me-2"></i>Finflow
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Accueil</a>
                <a class="nav-link" href="login.php">Connexion</a>
                <a class="nav-link" href="#">À propos</a>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="registration-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7 col-sm-9">
                    <div class="registration-card">
                        <div class="registration-header">
                            <h1>Créer un compte</h1>
                            <p>Rejoignez Finflow et prenez le contrôle de vos finances</p>
                        </div>

                        <form method="POST">
                            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
                            <div class="form-group">
                                <label for="name" class="form-label">username</label>
                                <div class="position-relative">
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Votre pseudo" required>
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <div class="position-relative">
                                    <input type="email" name="email" id="email" class="form-control" placeholder="votre@email.com" required>
                                    <i class="fas fa-envelope input-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="position-relative">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Créer un mot de passe" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="currency" class="form-label">Devise</label>
                                <div class="position-relative">
                                    <select name="currency" id="currency" class="form-control" required>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="USD">USD ($)</option>
                                        <option value="XOF">XOF (FCFA)</option>
                                    </select>
                                    <i class="fas fa-coins input-icon"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>
                                Créer mon compte
                            </button>
                        </form>

                        <div class="login-link">
                            <p style="color: rgba(255,255,255,0.7);">
                                Vous avez déjà un compte ?
                                <a href="login.php">Se connecter</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'Icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Input animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (password.length >= 8) {
                this.style.borderColor = 'rgba(40, 167, 69, 0.5)';
            } else {
                this.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            }
        });
    </script>
</body>
</html>