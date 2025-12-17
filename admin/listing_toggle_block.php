<?php
// admin/listing_toggle_block.php
require_once __DIR__ . '/../includes/auth.php';
init_auth($pdo);
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['listing_id'])) {
    $listingId = (int)$_POST['listing_id'];

    // Aktuellen Status abfragen
    $stmt = $pdo->prepare("SELECT is_blocked FROM listings WHERE id = :id");
    $stmt->execute([':id' => $listingId]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($listing) {
        $newStatus = $listing['is_blocked'] ? 0 : 1;
        $stmtUpdate = $pdo->prepare("UPDATE listings SET is_blocked = :status WHERE id = :id");
        $stmtUpdate->execute([':status' => $newStatus, ':id' => $listingId]);
    }
}

header('Location: index.php');
exit;
