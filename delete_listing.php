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

$id = $_GET['id'] ?? null;
if ($id === null || !ctype_digit($id)) {
    die("Ungültige ID");
}

$stmt = $pdo->prepare("
    SELECT * FROM listings
    WHERE id = :id AND user_id = :uid
");
$stmt->execute([
    ':id'  => $id,
    ':uid' => $_SESSION['user_id']
]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing nicht gefunden oder keine Berechtigung.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($listing['image_path'])) {
        $filePath = __DIR__ . '/' . $listing['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    $del = $pdo->prepare("DELETE FROM listings WHERE id = :id AND user_id = :uid");
    $del->execute([
        ':id'  => $listing['id'],
        ':uid' => $_SESSION['user_id']
    ]);

    header('Location: my_listings.php');
    exit;
}

$pageTitle = 'Listing löschen – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Listing löschen</h1>

<div class="card p-3 shadow-sm mb-3">
    <p>Bist du sicher, dass du dieses Listing löschen möchtest?</p>

    <p><strong><?= htmlspecialchars($listing['title']) ?></strong></p>

    <?php if (!empty($listing['image_path'])): ?>
        <p>
            <img src="<?= htmlspecialchars($listing['image_path']) ?>" alt="Karte"
                 style="max-width:200px;" class="img-thumbnail">
        </p>
    <?php endif; ?>

    <p>Preis: <?= htmlspecialchars($listing['price']) ?> €</p>

    <form method="post">
        <button class="btn btn-danger" type="submit">Ja, endgültig löschen</button>
        <a class="btn btn-outline-secondary ms-2" href="my_listings.php">Abbrechen</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
