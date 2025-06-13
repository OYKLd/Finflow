<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$userId = $_SESSION['user_id'];


// Solde actuel, total revenus et dépenses
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
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
            if ($row['type'] === 'income') {
                $income = $row['total'];
            } elseif ($row['type'] === 'expense') {
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
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <script src="/assets/js/chart.min.js"></script>
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Tableau de bord</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Solde actuel</h5>
                    <p class="card-text fs-4">
                        €<?= number_format($balance, 2, ',', ' ') ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total revenus</h5>
                    <p class="card-text fs-4">
                        €<?= number_format($totalIncome, 2, ',', ' ') ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total dépenses</h5>
                    <p class="card-text fs-4">
                        €<?= number_format($totalExpense, 2, ',', ' ') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Tendances mensuelles</h5>
            <canvas id="trendChart" height="100"></canvas>
        </div>
    </div>

    <div class="d-flex gap-3">
        <a href="/transactions/add.php" class="btn btn-success">Ajouter une transaction</a>
        <a href="/charts.php" class="btn btn-info">Voir les graphiques</a>
        <a href="/export.php?format=csv" class="btn btn-secondary">Exporter en CSV</a>
        <a href="/export.php?format=pdf" class="btn btn-secondary">Exporter en PDF</a>
    </div>
</div>

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