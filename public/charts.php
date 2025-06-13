<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';


$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT category, SUM(amount) AS total FROM transactions WHERE user_id = :user_id AND type = 'expense' GROUP BY category ORDER BY total DESC");
$stmt->execute(['user_id' => $userId]);
$categoryData = $stmt->fetchAll();

$categories = array_column($categoryData, 'category');
$totals = array_column($categoryData, 'total');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FinFlow - Graphiques</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="/assets/js/chart.min.js"></script>
</head>
<body>
<div class="container py-4">
    <h2>Graphique des dépenses par catégorie</h2>
    <canvas id="expenseChart" height="100"></canvas>
    <a href="dashboard.php" class="btn btn-secondary mt-4">Retour au tableau de bord</a>
</div>
<script>
    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($categories) ?>,
            datasets: [{
                data: <?= json_encode($totals) ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
</script>
</body>
</html>