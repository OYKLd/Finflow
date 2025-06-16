<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT name, avatar FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch();

if ($user) {
    $name = $user['name'];
    $avatar = $user['avatar'];
}

// Récupérer les informations de l'utilisateur, y compris la devise
$stmt = $pdo->prepare("SELECT currency FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch();

// Vérifier si l'utilisateur a été trouvé et si la devise est définie
if ($user && isset($user['currency'])) {
    $currency = $user['currency'];
} else {
    // Définir une devise par défaut si l'utilisateur n'est pas trouvé ou si la devise n'est pas définie
    $currency = '€'; // Euro par défaut
}

// Solde actuel, total revenus et dépenses
$stmt = $pdo->prepare("SELECT
    SUM(CASE WHEN type = 'revenu' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type = 'depense' THEN amount ELSE 0 END) AS total_expense
FROM transactions WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$totals = $stmt->fetch();

$totalIncome = $totals['total_income'] ?? 0;
$totalExpense = $totals['total_expense'] ?? 0;
$balance = $totalIncome - $totalExpense;

// Statistiques pour graphiques
$stmt = $pdo->prepare("SELECT
    type,
    DATE_FORMAT(date, '%Y-%m') AS month,
    SUM(amount) AS total
FROM transactions
WHERE user_id = :user_id
GROUP BY type, month
ORDER BY month ASC");
$stmt->execute(['user_id' => $userId]);
$stats = $stmt->fetchAll();

$labels = [];
$incomeData = [];
$expenseData = [];

foreach ($stats as $row) {
    $month = $row['month'];
    if (!in_array($month, $labels)) {
        $labels[] = $month;
    }
}

foreach ($labels as $month) {
    $income = 0;
    $expense = 0;
    foreach ($stats as $row) {
        if ($row['month'] === $month) {
            if ($row['type'] === 'revenu') {
                $income = $row['total'];
            } elseif ($row['type'] === 'depense') {
                $expense = $row['total'];
            }
        }
    }
    $incomeData[] = $income;
    $expenseData[] = $expense;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinFlow - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="/assets/js/chart.min.js"></script>
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
            <a class="navbar-brand" href="#">
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
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <img src="<?= htmlspecialchars($avatar ?? 'https://via.placeholder.com/30') ?>" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px;">
                            <?= htmlspecialchars($name ?? 'Profile') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <br>
                    <h2 class="display-4 fw-bold mb-4 animate-on-scroll">Tableau de bord</h2>
                    <p class="lead animate-on-scroll">Visualisez rapidement vos données financières</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h4 class="mb-3">Solde actuel</h4>
                        <p><?= htmlspecialchars($currency) ?> <?= number_format($balance, 2, ',', ' ') ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="mb-3">Total revenus</h4>
                        <p><?= htmlspecialchars($currency) ?> <?= number_format($totalIncome, 2, ',', ' ') ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h4 class="mb-3">Total dépenses</h4>
                        <p><?= htmlspecialchars($currency) ?> <?= number_format($totalExpense, 2, ',', ' ') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Actions Section -->
    <section id="actions" class="features">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <a href="add.php" class="btn btn-success">Ajouter une transaction</a>
                    <a href="charts.php" class="btn btn-info">Voir les graphiques</a>
                    <a href="export.php?format=csv" class="btn btn-secondary">Exporter en CSV</a>
                    <a href="export.php?format=pdf" class="btn btn-secondary">Exporter en PDF</a>
                </div>
            </div>
        </div>
    </section>

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
    <script>
        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Revenus',
                        data: <?= json_encode($incomeData) ?>,
                        borderColor: 'green',
                        fill: false
                    },
                    {
                        label: 'Dépenses',
                        data: <?= json_encode($expenseData) ?>,
                        borderColor: 'red',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                legend: { position: 'top' }
                }
            }
        });
    </script>
</body>
</html>