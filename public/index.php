<?php 
session_start();
require_once '../includes/header.php'; 
?>

<!-- Page d'accueil -->
<div class="container">
    <h1>Bienvenue sur Finflow</h1>
    <p>Gérez vos finances personnelles facilement et en toute sécurité.</p>
    
    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="about.php">À propos</a></li>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="public/login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            <?php else: ?>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Autres contenus de la page d'accueil -->
</div>

<?php require_once '../includes/footer.php'; ?>