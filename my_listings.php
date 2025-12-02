<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
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

$stmt = $pdo->prepare("
    SELECT * 
    FROM listings 
    WHERE user_id = :uid
    ORDER BY created_at DESC
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Meine Listings – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-3">Meine Listings</h1>

<p class="mb-3">
    Eingeloggt als:
    <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>
</p>

<a class="btn btn-success mb-3" href="create_listing.php">Neues Listing erstellen</a>

<?php if (empty($listings)): ?>
    <div class="alert alert-info">Du hast noch keine Listings erstellt.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($listings as $listing): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($listing['image_path'])): ?>
                        <img src="<?= htmlspecialchars($listing['image_path']) ?>"
                             class="card-img-top"
                             alt="Karte">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="card_detail.php?id=<?= $listing['id'] ?>">
                                <?= htmlspecialchars($listing['title']) ?>
                            </a>
                        </h5>
                        <p class="card-text mb-1">
                            Zustand: <?= htmlspecialchars($listing['card_condition']) ?>
                        </p>
                        <p class="card-text fw-bold">
                            Preis: <?= htmlspecialchars($listing['price']) ?> €
                        </p>
                        <p class="card-text small">
                            <?= nl2br(htmlspecialchars($listing['description'])) ?>
                        </p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a class="btn btn-sm btn-outline-primary"
                           href="edit_listing.php?id=<?= $listing['id'] ?>">
                            Bearbeiten
                        </a>
                        <a class="btn btn-sm btn-outline-danger"
                           href="delete_listing.php?id=<?= $listing['id'] ?>"
                           onclick="return confirm('Listing wirklich löschen?');">
                            Löschen
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
