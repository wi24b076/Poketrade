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
    SELECT 
        l.*, 
        u.username,
        u.email AS owner_email,
        u.id AS owner_id
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.id = :id
");
$stmt->execute([':id' => $id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing nicht gefunden");
}

// Helper-Flags
$isLoggedIn = !empty($_SESSION['user_id']);
$isOwner    = $isLoggedIn && ((int)$_SESSION['user_id'] === (int)$listing['user_id']);
$isFavorite = false;

// Prüfen, ob dieses Listing schon Favorit ist (nur für eingeloggte Nicht-Owner)
if ($isLoggedIn && !$isOwner) {
    $stmtFav = $pdo->prepare("
        SELECT 1
        FROM favorites
        WHERE user_id = :uid AND listing_id = :lid
    ");
    $stmtFav->execute([
        ':uid' => (int)$_SESSION['user_id'],
        ':lid' => (int)$listing['id']
    ]);
    $isFavorite = (bool)$stmtFav->fetchColumn();
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

        <?php if ($isOwner): ?>
            <a class="btn btn-primary me-2" href="edit_listing.php?id=<?= $listing['id'] ?>">Bearbeiten</a>
            <a class="btn btn-danger"
               href="delete_listing.php?id=<?= $listing['id'] ?>"
               onclick="return confirm('Listing wirklich löschen?');">
                Löschen
            </a>
        <?php endif; ?>

        <?php if ($isLoggedIn && !$isOwner): ?>
            <!-- Favoriten-Button -->
            <form method="post"
                  action="/Poketrade/favorite_toggle.php"
                  class="d-inline">
                <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                <input type="hidden" name="redirect"
                       value="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES) ?>">
                <button type="submit" class="btn btn-warning">
                    <?= $isFavorite ? '★ Favorit entfernen' : '☆ Zu Favoriten' ?>
                </button>
            </form>
        <?php endif; ?>

        <!-- Kontakt-Box -->
        <div class="card mt-4">
            <div class="card-header">
                Anbieter kontaktieren
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong><?= htmlspecialchars($listing['username']) ?></strong>
                </p>

                <?php if ($isOwner): ?>
                    <p class="text-muted mb-0">
                        Das ist dein eigenes Listing – du kannst es oben bearbeiten oder löschen.
                    </p>

                <?php elseif ($isLoggedIn): ?>
                    <?php
                    $subject = rawurlencode('Anfrage zu deinem Poketrade-Listing: ' . $listing['title']);
                    $body = rawurlencode(
                        "Hallo " . $listing['username'] . ",\n\n" .
                        "ich interessiere mich für deine Karte \"" . $listing['title'] . "\" auf Poketrade.\n\n" .
                        "Viele Grüße,\n" .
                        ($_SESSION['username'] ?? 'ein Poketrade-Nutzer')
                    );
                    $mailto = "mailto:" . rawurlencode($listing['owner_email']) . "?subject={$subject}&body={$body}";
                    ?>
                    <a href="<?= $mailto ?>" class="btn btn-primary">
                        Per E-Mail kontaktieren
                    </a>

                <?php else: ?>
                    <p class="text-muted mb-2">
                        Bitte logge dich ein, um den Anbieter zu kontaktieren.
                    </p>
                    <a href="login.php" class="btn btn-outline-primary btn-sm">
                        Zum Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
