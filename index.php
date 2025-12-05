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

    <!-- Hero-Text unter dem Carousel -->
    <div class="mb-4 p-4 bg-light rounded-3 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 mb-3">Willkommen bei Poketrade</h1>
                <p class="lead mb-0">
                    Die Plattform für den Tausch und Verkauf von Pokémon-Karten.
                    Erstelle eigene Listings, finde seltene Karten und merke dir Favoriten.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="browse.php" class="btn btn-primary mb-2">Alle Listings ansehen</a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="create_listing.php" class="btn btn-outline-success">Neues Listing erstellen</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-outline-success">Jetzt registrieren</a>
                <?php endif; ?>
            </div>
        </div>
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
    <h2 class="mb-3">Beliebte Karten</h2>

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
                                <img src="<?= htmlspecialchars($card['image_path']) ?>"
                                     class="card-img-top"
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
                            <strong><?= number_format((float)$card['price'], 2, ',', '.') ?> €</strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
