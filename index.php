<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Startseite – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-4 bg-white rounded shadow-sm">
    <h1 class="mb-3">Willkommen bei Poketrade</h1>
    <p class="lead">
        Die Plattform für den Tausch von Pokemon-Karten.
    </p>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <p class="mb-3">
            Schön, dass du wieder da bist,
            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!
        </p>
        <a class="btn btn-primary me-2" href="browse.php">Alle Listings ansehen</a>
        <a class="btn btn-success" href="create_listing.php">Neues Listing erstellen</a>
    <?php else: ?>
        <p class="mb-3">
            Logge dich ein oder registriere dich, um eigene Listings zu erstellen.
        </p>
        <a class="btn btn-primary me-2" href="login.php">Login</a>
        <a class="btn btn-success" href="register.php">Registrieren</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
