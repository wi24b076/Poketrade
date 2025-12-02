<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

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
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Browse – Poketrade</title>
</head>
<body>
<h1>Alle Listings</h1>

<?php if (empty($listings)): ?>
    <p>Noch keine Listings vorhanden.</p>
<?php else: ?>
    <?php foreach ($listings as $listing): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <h3><?= htmlspecialchars($listing['title']) ?></h3>
            <p>von <?= htmlspecialchars($listing['username']) ?></p>
            <p>Zustand: <?= htmlspecialchars($listing['card_condition']) ?></p>
            <p>Preis: <?= htmlspecialchars($listing['price']) ?> €</p>
            <?php if (!empty($listing['image_path'])): ?>
                <div>
                    <img src="<?= htmlspecialchars($listing['image_path']) ?>" alt="Karte"
                         style="max-width:200px;">
                </div>
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
