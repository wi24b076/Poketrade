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

$id = $_GET['id'] ?? null;
if ($id === null || !ctype_digit($id)) {
    die("Ungültige ID");
}

$stmt = $pdo->prepare("
    SELECT l.*, u.username
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.id = :id
");
$stmt->execute([':id' => $id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing nicht gefunden");
}

$pageTitle = htmlspecialchars($listing['title']) . ' – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-md-5">
        <?php if (!empty($listing['image_path'])): ?>
            <img src="<?= htmlspecialchars($listing['image_path']) ?>"
                 alt="Karte"
                 class="img-fluid rounded shadow-sm mb-3">
        <?php else: ?>
            <div class="alert alert-secondary">Kein Bild vorhanden.</div>
        <?php endif; ?>
    </div>
    <div class="col-md-7">
        <h1 class="mb-3"><?= htmlspecialchars($listing['title']) ?></h1>
        <p class="text-muted mb-1">
            von <strong><?= htmlspecialchars($listing['username']) ?></strong>
        </p>
        <p class="mb-1">
            Zustand: <strong><?= htmlspecialchars($listing['card_condition']) ?></strong>
        </p>
        <p class="mb-3">
            Preis: <span class="fw-bold"><?= htmlspecialchars($listing['price']) ?> €</span>
        </p>

        <h5>Beschreibung</h5>
        <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>

        <a class="btn btn-outline-secondary me-2" href="browse.php">Zurück zu allen Listings</a>

        <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $listing['user_id']): ?>
            <a class="btn btn-primary me-2" href="edit_listing.php?id=<?= $listing['id'] ?>">Bearbeiten</a>
            <a class="btn btn-danger"
               href="delete_listing.php?id=<?= $listing['id'] ?>"
               onclick="return confirm('Listing wirklich löschen?');">
                Löschen
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
