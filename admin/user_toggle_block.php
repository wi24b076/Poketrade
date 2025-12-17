<?php
// admin/user_toggle_block.php
require_once __DIR__ . '/../includes/auth.php';
init_auth($pdo);
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];

    // Aktuellen Status abfragen
    $stmt = $pdo->prepare("SELECT is_blocked FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $newStatus = $user['is_blocked'] ? 0 : 1;
        $stmtUpdate = $pdo->prepare("UPDATE users SET is_blocked = :status WHERE id = :id");
        $stmtUpdate->execute([':status' => $newStatus, ':id' => $userId]);
    }
}

header('Location: index.php');
exit;
