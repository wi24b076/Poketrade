<?php
// favorites.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /Poketrade/login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        l.*, 
        u.username AS owner_username
    FROM favorites f
    JOIN listings l ON f.listing_id = l.id
    JOIN users u ON l.user_id = u.id
    WHERE f.user_id = :uid
    ORDER BY f.created_at DESC
");
$stmt->execute([':uid' => $userId]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Meine Favoriten – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Meine Favoriten</h1>

<?php if (empty($listings)): ?>
    <div class="alert alert-info">
        Du hast noch keine Favoriten.  
        <a href="/Poketrade/browse.php">Zu den Listings</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($listings as $listing): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($listing['image_path'])): ?>
                        <img src="<?= htmlspecialchars($listing['image_path']) ?>"
                             class="card-img-top"
                             alt="Karte">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <?= htmlspecialchars($listing['title']) ?>
                        </h5>
                        <p class="card-text text-muted mb-1">
                            von <?= htmlspecialchars($listing['owner_username']) ?>
                        </p>
                        <p class="card-text mb-2">
                            Zustand:
                            <strong><?= htmlspecialchars($listing['card_condition']) ?></strong>
                        </p>
                        <p class="card-text fw-bold mb-3">
                            <?= htmlspecialchars($listing['price']) ?> €
                        </p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <a href="/Poketrade/card_detail.php?id=<?= $listing['id'] ?>"
                               class="btn btn-sm btn-outline-secondary">
                                Details
                            </a>

                            <form method="post" action="/Poketrade/favorite_toggle.php" class="m-0">
                                <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                <input type="hidden" name="redirect" value="/Poketrade/favorites.php">
                                <button type="submit" class="btn btn-sm btn-warning">
                                    ★ Entfernen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
