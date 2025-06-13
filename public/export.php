<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;

$format = $_GET['format'] ?? 'csv';
$userId = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT type, amount, description, category, date FROM transactions WHERE user_id = :user_id ORDER BY date DESC");
$stmt->execute(['user_id' => $userId]);
$data = $stmt->fetchAll();

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Type', 'Montant', 'Description', 'Catégorie', 'Date']);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
} elseif ($format === 'pdf') {


    $html = '<h1>Transactions</h1><table border="1" width="100%" cellspacing="0" cellpadding="5">
        <tr><th>Type</th><th>Montant</th><th>Description</th><th>Catégorie</th><th>Date</th></tr>';
    foreach ($data as $row) {
        $html .= '<tr><td>' . htmlspecialchars($row['type']) . '</td><td>' . number_format($row['amount'], 2) . '</td><td>' . htmlspecialchars($row['description']) . '</td><td>' . htmlspecialchars($row['category']) . '</td><td>' . htmlspecialchars($row['date']) . '</td></tr>';
    }
    $html .= '</table>';

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('transactions.pdf');
    exit;
}

header('Location: dashboard.php');
exit;