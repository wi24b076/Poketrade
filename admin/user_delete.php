<?php
// admin/user_delete.php
require_once __DIR__ . '/../includes/auth.php';
init_auth($pdo);
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];

    // Optional: Listings des Benutzers löschen
    $stmtListings = $pdo->prepare("DELETE FROM listings WHERE user_id = :uid");
    $stmtListings->execute([':uid' => $userId]);

    // Benutzer löschen
    $stmtUser = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmtUser->execute([':id' => $userId]);
}

header('Location: index.php');
exit;
