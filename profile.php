<?php
// profile.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

init_auth($pdo); // Auto-Login versuchen

// Login erforderlich
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Account löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    // Benutzer löschen
    $uid = (int)$_SESSION['user_id'];

    $pdo->prepare("DELETE FROM listings WHERE user_id = :uid")->execute([':uid' => $uid]);
    $pdo->prepare("DELETE FROM favorites WHERE user_id = :uid")->execute([':uid' => $uid]);
    $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = :uid")->execute([':uid' => $uid]);
    $pdo->prepare("DELETE FROM users WHERE id = :uid")->execute([':uid' => $uid]);

    // Session beenden
    $_SESSION = [];
    session_destroy();

    header('Location: index.php');
    exit;
}

// Benutzerinfos laden
$stmt = $pdo->prepare("SELECT username, email, role, created_at FROM users WHERE id = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Mein Profil – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Mein Profil</h1>

    <div class="card shadow-sm p-3 mb-4">
        <h5>Benutzerinformationen</h5>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>E-Mail:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Rolle:</strong> <?= htmlspecialchars($user['role']) ?></p>
        <p><strong>Registriert seit:</strong> <?= htmlspecialchars(date('d.m.Y', strtotime($user['created_at']))) ?></p>
    </div>

    <div class="mb-4">
        <a href="my_listings.php" class="btn btn-primary me-2">Meine Listings</a>
        <a href="favorites.php" class="btn btn-warning me-2">Meine Favoriten</a>
    </div>

    <div class="card shadow-sm p-3">
        <h5>Account löschen</h5>
        <p>Wenn du deinen Account löschst, werden alle deine Daten einschließlich Listings und Favoriten dauerhaft entfernt. Dies kann nicht rückgängig gemacht werden.</p>

        <form method="post" onsubmit="return confirm('Willst du deinen Account wirklich löschen?');">
            <button type="submit" name="delete_account" class="btn btn-danger w-100">
                Account löschen
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
