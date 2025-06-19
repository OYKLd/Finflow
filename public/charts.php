<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$userId = $_SESSION['user_id'];

// Récupérer les infos utilisateur
$stmt = $pdo->prepare("SELECT name, avatar FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch();

$name = $user['name'] ?? 'Profil';
$avatar = $user['avatar'] ?? 'https://via.placeholder.com/30';

// Récupérer les données de dépenses par catégorie
$stmt = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions WHERE user_id = :user_id AND type = 'depense' GROUP BY category ORDER BY total DESC");
$stmt->execute(['user_id' => $userId]);
$categoryData = $stmt->fetchAll();

$categories = array_column($categoryData, 'category');
$totals = array_map('floatval', array_column($categoryData, 'total')); // Assure que ce sont des nombres
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinFlow - Graphiques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
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
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            padding: 20px 15px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-bottom: 15px;

            max-width: 500px; 
            margin-left: auto;
            margin-right: auto;
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
    </style>
</head>
        
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top bg-dark navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-chart-line me-2"></i>Finflow</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaction.php">Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">À propos</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px;">
                            <?= htmlspecialchars($name) ?>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link btn btn-danger" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero mt-5 pt-5">
        <div class="container text-center">
            <h1>Graphiques</h1>
            <p>Visualisation de vos dépenses par catégorie.</p>
        </div>
    </section>

    <!-- Graphique -->
    <section class="my-5">
        <div class="container">
            <?php if (empty($categoryData)): ?>
                <div class="alert alert-warning text-center">Aucune donnée de dépenses disponible.</div>
            <?php else: ?>
                <div class="card p-4">
                    <canvas id="expenseChart" height="200"></canvas>
                    <div class="text-center mt-3">
                        <a href="dashboard.php" class="btn btn-secondary">Retour</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center mt-5">
        <p class="mb-0">&copy; 2025 Finflow - Votre partenaire financier de confiance</p>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Données PHP vers JavaScript
        const categories = <?= json_encode($categories) ?>;
        const totals = <?= json_encode($totals) ?>;

        const ctx = document.getElementById('expenseChart')?.getContext('2d');

        if (ctx && categories.length && totals.length) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categories,
                    datasets: [{
                        data: totals,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56',
                            '#4BC0C0', '#9966FF', '#FF9F40',
                            '#B9FBC0', '#A0C4FF', '#FFD6A5'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Répartition des dépenses par catégorie'
                        }
                    }
                }
            });
        } else {
            console.warn('Aucune donnée disponible pour afficher le graphique.');
        }
    </script>
</body>
</html>
