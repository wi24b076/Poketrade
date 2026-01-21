<?php
// admin/listing_delete.php
require_once __DIR__ . '/../includes/auth.php';
init_auth($pdo);
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['listing_id'])) {
    $listingId = (int)$_POST['listing_id'];

    $stmt = $pdo->prepare("DELETE FROM listings WHERE id = :id");
    $stmt->execute([':id' => $listingId]);
}

header('Location: index.php');
exit;
