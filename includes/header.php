<?php
// auth.php einbinden. Dadurch:
// - wird die Session gestartet
// - wird die Datenbankverbindung ($pdo) hergestellt
// - stehen Funktionen wie init_auth(), require_login() usw. zur Verfügung
require_once __DIR__ . '/auth.php';

// Führe den Auto-Login-Versuch aus.
// Wenn gültige Remember-Me Cookies existieren, wird automatisch $_SESSION gesetzt.
init_auth($pdo);

// Wenn die Variable $pageTitle vorher nicht gesetzt wurde, verwende "Poketrade" als Standardtitel.
// Der ?? Operator bedeutet: "Nimm links, wenn nicht null/undefiniert, sonst rechts."
$pageTitle = $pageTitle ?? 'Poketrade';
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8"> <!-- UTF-8 Zeichencodierung -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Responsives Design -->

    <!-- Titel der Seite, sicher ausgegeben (XSS-Schutz durch htmlspecialchars) -->
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Icon im Browser-Tab -->
    <link rel="icon" type="image/png" href="/Poketrade/assets/favicon.png">

    <!-- Bootstrap 5 CSS von CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Dein eigenes CSS -->
    <link rel="stylesheet" href="/Poketrade/assets/css/style.css">
</head>

<body>
    <!-- Navigationsleiste oben -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">

            <!-- Logo, immer verlinkt auf die Startseite -->
            <a class="navbar-brand d-flex align-items-center" href="/Poketrade/index.php">
                <img src="/Poketrade/assets/logo.png" alt="Poketrade Logo" style="height:60px; width:auto;">
            </a>

            <!-- Mobile Navigation (Hamburger-Menü) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menü-Inhalt -->
            <div class="collapse navbar-collapse" id="mainNavbar">

                <!-- Link-Liste links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <!-- Immer sichtbar -->
                    <li class="nav-item">
                        <a class="nav-link pokemon-font" href="/Poketrade/browse.php">Alle Listings</a>
                    </li>

                    <!-- Nur sichtbar, wenn Benutzer eingeloggt ist -->
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

                        <!-- Nur sichtbar für Admins -->
                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link pokemon-font" href="/Poketrade/admin/index.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>

                </ul>

                <!-- Rechts ausgerichtete Navigation -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                    <!-- Wenn eingeloggt -->
                    <?php if (!empty($_SESSION['user_id'])): ?>

                        <!-- Benutzername anzeigen -->
                        <li class="nav-item">
                            <div class="navbar-text me-3">
                                Eingeloggt als <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                            </div>
                        </li>

                        <!-- Logout Button -->
                        <li class="nav-item d-flex align-items-center">
                            <a class="btn btn-outline-light btn-sm" href="/Poketrade/logout.php">Logout</a>
                        </li>

                        <!-- Wenn NICHT eingeloggt -->
                    <?php else: ?>

                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm me-2" href="/Poketrade/login.php">Login</a>
                        </li>

                        <li class="nav-item">
                            <a class="btn btn-warning btn-sm" href="/Poketrade/register.php">Registrieren</a>
                        </li>

                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Hauptinhalt des Dokuments beginnt hier -->
    <main class="container py-4">