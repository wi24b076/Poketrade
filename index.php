<?php
// index.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Auth-System inkl. $pdo laden
require_once __DIR__ . '/includes/auth.php';

// Auto-Login über Remember-Me-Cookies versuchen
init_auth($pdo);

// Seitentitel für <title>
$pageTitle = 'Willkommen bei Poketrade';

// Header einbinden (Navbar etc.)
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">


    <!-- Hero-Text unter Header-->
    <div class="mb-4 p-4 bg-light rounded-3 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 mb-3">Willkommen bei Poketrade</h1>
                <p class="lead mb-0">
                    Die Plattform für den Tausch und Verkauf von Pokémon-Karten.
                    Erstelle eigene Listings, finde seltene Karten und merke dir Favoriten.
                </p>
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">

                    <a href="browse.php" class="btn btn-primary">
                        Alle Listings ansehen
                    </a>

                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <a href="create_listing.php" class="btn btn-outline-success">
                            Neues Listing erstellen
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-outline-success">
                            Jetzt registrieren
                        </a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <div id="carouselExampleIndicators" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100 carousel-img" src="assets/carousel_images/image1.png" alt="First slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100 carousel-img" src="assets/carousel_images/image2.jpg" alt="Second slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100 carousel-img" src="assets/carousel_images/image3.png" alt="Third slide">
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <?php
    // 6 neueste Karten laden
    $stmt = $pdo->query("
        SELECT l.*, u.username
        FROM listings l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 6
    ");
    $featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Beliebte / neue Karten -->
    <h2 class="mb-3">Neueste Karten</h2>

    <?php if (empty($featured)): ?>
        <div class="alert alert-info">
            Noch keine Karten online. Lege jetzt das erste Listing an!
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($featured as $card): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card shadow-sm h-100">
                        <a href="card_detail.php?id=<?= $card['id'] ?>">
                            <?php if (!empty($card['image_path'])): ?>
                                <img src="<?= htmlspecialchars($card['image_path']) ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($card['title']) ?>">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                    style="height: 160px;">
                                    Keine Vorschau
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title mb-1" style="font-size: 0.9rem;">
                                <?= htmlspecialchars($card['title']) ?>
                            </h6>
                            <p class="text-muted mb-1" style="font-size: 0.8rem;">
                                <?= htmlspecialchars($card['card_condition']) ?>
                            </p>
                            <strong><?= number_format((float) $card['price'], 2, ',', '.') ?> €</strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
