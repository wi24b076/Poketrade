<?php
require_once __DIR__ . '/auth.php';

// Auto-Login über Remember-Me-Cookie (setzt ggf. $_SESSION)
init_auth($pdo);

// Seitentitel setzen, wenn nicht definiert
$pageTitle = $pageTitle ?? 'Poketrade';
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="/Poketrade/assets/favicon.png">


    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="/Poketrade/assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Logo immer zur Startseite -->
            <a class="navbar-brand d-flex align-items-center" href="/Poketrade/index.php">
                <img src="/Poketrade/assets/logo.png" alt="Poketrade Logo" style="height:60px; width:auto;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link pokemon-font" href="/Poketrade/browse.php">Alle Listings</a>
                    </li>

                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link pokemon-font" href="/Poketrade/my_listings.php">Meine Listings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link pokemon-font" href="/Poketrade/create_listing.php">Listing erstellen</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link pokemon-font" href="/Poketrade/favorites.php">Favoriten</a>
                        </li>

                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link pokemon-font" href="/Poketrade/admin/index.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                </ul>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="navbar-text me-3">
                                Eingeloggt als <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm" href="/Poketrade/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light btn-sm" href="/Poketrade/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-warning btn-sm" href="/Poketrade/register.php">Registrieren</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">