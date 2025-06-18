<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_start();
$userId = $_SESSION['user_id'] ?? null;
$success = false;
$error = false;

if ($userId) {
    $stmt = $pdo->prepare("SELECT name, avatar FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $user = $stmt->fetch();

    if ($user) {
        $name = $user['name'];
        $avatar = $user['avatar'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $avis = trim($_POST['avis'] ?? '');

    if ($nom && $email && $avis) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'lydieouattara828@gmail.com'; // Remplacez
            $mail->Password = 'iiua ckvp wfww pbod'; // Utilisez un mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('lydieouattara828@gmail.com', 'Finflow Feedback');
            $mail->addAddress('lydieouattara828@gmail.com');
            $mail->addReplyTo($email, $nom);

            $mail->Subject = 'Nouvel avis utilisateur Finflow';
            $mail->Body = "Nom: $nom\nEmail: $email\nAvis:\n$avis";

            $mail->send();
            $success = true;
        } catch (Exception $e) {
            $error = true;
        }
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>À propos - Finflow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            color: #fff;
            line-height: 1.6;
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
            animation: gradientShift 10s ease-in-out infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            50% { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        }
        
        /* Floating Shapes */
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
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            animation: float 20s infinite linear;
            backdrop-filter: blur(2px);
        }
        
        .shape:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 120px; height: 120px; left: 80%; animation-delay: 5s; }
        .shape:nth-child(3) { width: 60px; height: 60px; left: 50%; animation-delay: 10s; }
        .shape:nth-child(4) { width: 100px; height: 100px; left: 20%; animation-delay: 15s; }
        .shape:nth-child(5) { width: 140px; height: 140px; left: 70%; animation-delay: 8s; }
        .shape:nth-child(6) { width: 90px; height: 90px; left: 30%; animation-delay: 12s; }
        
        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10%, 90% { opacity: 1; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }
        
        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(10,10,10,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-link {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        .nav-link:hover {
            color: #667eea;
            background: rgba(102,126,234,0.1);
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.4);
        }
        
        /* Main Content */
        .main-content {
            margin-top: 100px;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Hero Section */
        .hero-section {
            background: rgba(10,10,10,0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 20px 60px rgba(102,126,234,0.1);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #667eea, transparent);
        }
        
        .hero-content {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: center;
        }
        
        .profile-section {
            text-align: center;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102,126,234,0.3);
        }
        
        .profile-img:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(102,126,234,0.4);
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        
        .social-icons a:hover {
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        
        .hero-text h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .hero-text p {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 1.5rem;
        }
        
        /* Content Sections */
        .content-section {
            background: rgba(10,10,10,0.8);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(102,126,234,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .content-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(102,126,234,0.15);
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .feature-card {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            background: rgba(102,126,234,0.1);
            transform: translateY(-3px);
        }
        
        .feature-card h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .tech-item {
            background: rgba(102,126,234,0.2);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid rgba(102,126,234,0.3);
            transition: all 0.3s ease;
        }
        
        .tech-item:hover {
            background: rgba(102,126,234,0.3);
            transform: scale(1.05);
        }
        
        /* Contact Form */
        .contact-form {
            background: rgba(10,10,10,0.8);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(102,126,234,0.08);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #fff;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            background: rgba(255,255,255,0.1);
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }
        
        .form-control::placeholder {
            color: rgba(255,255,255,0.5);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102,126,234,0.4);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        /* Footer */
        .footer {
            background: rgba(10,10,10,0.95);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 2rem 0;
            text-align: center;
            margin-top: 3rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1.5rem;
            }
            
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .hero-section,
            .content-section,
            .contact-form {
                padding: 1.5rem;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid;
        }
        
        .alert-success {
            background: rgba(40,167,69,0.2);
            border-color: rgba(40,167,69,0.3);
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220,53,69,0.2);
            border-color: rgba(220,53,69,0.3);
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="navbar-brand">
                <i class="fas fa-chart-line"></i>
                Finflow
            </a>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link">Tableau de bord</a></li>
                <li><a href="charts.php" class="nav-link">Graphiques</a></li>
                <li><a href="transactions.php" class="nav-link">Transactions</a></li>
                <li><a href="profile.php"><img src="<?= htmlspecialchars($avatar ?? 'https://via.placeholder.com/30') ?>" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px;"><?= htmlspecialchars($name ?? 'Profile') ?></a></li>                <li><button class="btn-logout">Déconnexion</button></li>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="profile-section">
                    <img src="http://localhost/Finflow/public/1734215257902.jpg" alt="Lydie Ouattara" class="profile-img">
                    <div class="social-icons">
                        <a href="https://www.linkedin.com/in/lydie-ouattara-623710312" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="mailto:lydieouattara828@gmail.com" title="Email"><i class="fas fa-envelope"></i></a>
                        <a href="https://wa.me/2250719749695" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="hero-text">
                    <p>
                        Salut ! Moi c'est <strong>Lydie Ouattara</strong> — développeuse web passionnée, curieuse, et toujours partante pour transformer une idée en solution concrète.
                    </p>
                    <p>
                        Ce projet de gestion financière est né dans le cadre de ma formation DEVWEB, mais aussi d'un constat simple : aujourd'hui, on a tous besoin d'un outil clair et pratique pour gérer son argent sans prise de tête.
                    </p>
                </div>
            </div>
        </section>
        
        <!-- Project Section -->
        <section class="content-section">
            <h2 class="section-title">🎯 Le projet : une application de gestion financière</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>💰 Suivi des revenus</h3>
                    <p>Enregistrez vos revenus avec montant, source, description et date pour un suivi complet.</p>
                </div>
                <div class="feature-card">
                    <h3>💸 Gestion des dépenses</h3>
                    <p>Catégorisez et suivez toutes vos dépenses pour mieux comprendre vos habitudes.</p>
                </div>
                <div class="feature-card">
                    <h3>📊 Tableau de bord</h3>
                    <p>Visualisez vos finances avec des graphiques clairs et des statistiques détaillées.</p>
                </div>
                <div class="feature-card">
                    <h3>📈 Export de données</h3>
                    <p>Exportez vos données en CSV ou PDF pour vos analyses personnelles.</p>
                </div>
                <div class="feature-card">
                    <h3>🔒 Sécurité avancée</h3>
                    <p>Vos données sont chiffrées et protégées avec les meilleures pratiques de sécurité.</p>
                </div>
            </div>
            <p style="margin-top: 2rem;">
                Cette solution vise à aider chacun à <strong>reprendre le contrôle de ses finances</strong>. 
                Parce que gérer ses finances ne devrait pas être réservé aux experts ou aux grosses applis bancaires.
            </p>
        </section>
        
        <!-- Technologies Section -->
        <section class="content-section">
            <h2 class="section-title">🧰 Technologies utilisées</h2>
            <div class="tech-stack">
                <span class="tech-item">HTML5</span>
                <span class="tech-item">CSS3</span>
                <span class="tech-item">PHP</span>
                <span class="tech-item">PHPMAILER</span>
                <span class="tech-item">MySQL</span>
                <span class="tech-item">jQuery</span>
                <span class="tech-item">Chart.js</span>
            </div>
            <p style="margin-top: 1.5rem;">
                Une stack moderne et éprouvée pour une application web performante, sécurisée et responsive sur tous les appareils.
            </p>
        </section>
        
        <!-- Security Section -->
        <section class="content-section">
            <h2 class="section-title">🔒 Sécurité et accessibilité</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>🛡️ Authentification sécurisée</h3>
                    <p>Système d'authentification robuste avec email et mot de passe chiffré.</p>
                </div>
                <div class="feature-card">
                    <h3>🔐 Chiffrement des données</h3>
                    <p>Toutes les données sensibles sont chiffrées selon les standards de l'industrie.</p>
                </div>
                <div class="feature-card">
                    <h3>📱 Design responsive</h3>
                    <p>Interface adaptée aux ordinateurs, tablettes et smartphones.</p>
                </div>
            </div>
        </section>
        
        <!-- Role Section -->
        <section class="content-section">
            <h2 class="section-title">👨‍💻 Mon rôle</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>📋 Cahier des charges</h3>
                    <p>Analyse des besoins et définition des spécifications techniques.</p>
                </div>
                <div class="feature-card">
                    <h3>🎨 Conception UX/UI</h3>
                    <p>Création des maquettes et de l'expérience utilisateur.</p>
                </div>
                <div class="feature-card">
                    <h3>💻 Développement Full-Stack</h3>
                    <p>Développement complet du frontend et du backend.</p>
                </div>
                <div class="feature-card">
                    <h3>🧪 Tests et qualité</h3>
                    <p>Mise en place de tests unitaires et tests utilisateurs.</p>
                </div>
                <div class="feature-card">
                    <h3>🔧 Optimisation</h3>
                    <p>Intégration de mesures de sécurité et d'optimisation des performances.</p>
                </div>
            </div>
        </section>
        
        <!-- Contact Form -->
        <section class="contact-form">
            <h2 class="section-title">💬 Donnez votre avis</h2>
            <form method="POST" id="feedbackForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="votre@email.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Votre avis</label>
                    <textarea class="form-control" name="avis" rows="4" placeholder="Partagez votre avis sur l'application..." required></textarea>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                    Envoyer mon avis
                </button>
            </form>
        </section>
    </div>
    
    <footer class="footer">
        <div class="nav-container">
            <p>&copy; 2025 Finflow - Votre partenaire financier de confiance</p>
        </div>
    </footer>
    
    <script>        
        // Smooth scrolling for navigation
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
        
        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(10,10,10,0.98)';
            } else {
                navbar.style.background = 'rgba(10,10,10,0.95)';
            }
        });
    </script>
</body>
</html>