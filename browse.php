<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'poketrade';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB-Fehler: " . $e->getMessage());
}

$stmt = $pdo->query("
    SELECT l.*, u.username
    FROM listings l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
");
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Favoriten des eingeloggten Users vorladen
$favIds = [];
if (!empty($_SESSION['user_id'])) {
    $stmtFav = $pdo->prepare("
        SELECT listing_id
        FROM favorites
        WHERE user_id = :uid
    ");
    $stmtFav->execute([':uid' => (int)$_SESSION['user_id']]);
    $favIds = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

$pageTitle = 'Alle Listings – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Alle Listings</h1>

<?php if (empty($listings)): ?>
    <div class="alert alert-info">Noch keine Listings vorhanden.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($listings as $listing): ?>
            <?php
            $isLoggedIn = !empty($_SESSION['user_id']);
            $isOwner    = $isLoggedIn && ((int)$_SESSION['user_id'] === (int)$listing['user_id']);
            $isFav      = $isLoggedIn && in_array($listing['id'], $favIds);
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($listing['image_path'])): ?>
                        <img src="<?= htmlspecialchars($listing['image_path']) ?>"
                             class="card-img-top"
                             alt="Karte">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="card_detail.php?id=<?= $listing['id'] ?>">
                                <?= htmlspecialchars($listing['title']) ?>
                            </a>
                        </h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            von <?= htmlspecialchars($listing['username']) ?>
                        </h6>
                        <p class="card-text mb-1">
                            Zustand: <?= htmlspecialchars($listing['card_condition']) ?>
                        </p>
                        <p class="card-text fw-bold">
                            Preis: <?= htmlspecialchars($listing['price']) ?> €
                        </p>
                        <p class="card-text small">
                            <?= nl2br(htmlspecialchars($listing['description'])) ?>
                        </p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <a href="card_detail.php?id=<?= $listing['id'] ?>"
                               class="btn btn-sm btn-outline-secondary">
                                Details
                            </a>

                            <?php if ($isLoggedIn && !$isOwner): ?>
                                <form method="post"
                                      action="/Poketrade/favorite_toggle.php"
                                      class="m-0">
                                    <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                                    <input type="hidden" name="redirect" value="/Poketrade/browse.php">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-warning">
                                        <?= $isFav ? '★' : '☆' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
