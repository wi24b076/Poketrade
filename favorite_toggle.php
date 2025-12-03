<?php
// favorite_toggle.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /Poketrade/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Poketrade/browse.php');
    exit;
}

$listingId = $_POST['listing_id'] ?? null;
$redirect  = $_POST['redirect'] ?? '/Poketrade/browse.php';

if ($listingId === null || !ctype_digit($listingId)) {
    header('Location: /Poketrade/browse.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

require_once __DIR__ . '/config/db.php'; // erwartet $pdo

// Prüfen, ob Favorit schon existiert
$stmt = $pdo->prepare("
    SELECT 1 
    FROM favorites 
    WHERE user_id = :uid AND listing_id = :lid
");
$stmt->execute([
    ':uid' => $userId,
    ':lid' => $listingId
]);

if ($stmt->fetch()) {
    // schon Favorit → entfernen (un-fav)
    $del = $pdo->prepare("
        DELETE FROM favorites
        WHERE user_id = :uid AND listing_id = :lid
    ");
    $del->execute([
        ':uid' => $userId,
        ':lid' => $listingId
    ]);
} else {
    // noch kein Favorit → hinzufügen
    $ins = $pdo->prepare("
        INSERT INTO favorites (user_id, listing_id)
        VALUES (:uid, :lid)
    ");
    $ins->execute([
        ':uid' => $userId,
        ':lid' => $listingId
    ]);
}

// Redirect nur intern erlauben
if (strpos($redirect, '/Poketrade/') !== 0) {
    $redirect = '/Poketrade/browse.php';
}

header('Location: ' . $redirect);
exit;
