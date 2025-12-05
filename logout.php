<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

init_auth($pdo);

// Remember-Me-Token im DB löschen (falls vorhanden)
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = :uid");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
}

// Cookies invalidieren
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$past   = time() - 3600;

setcookie('remember_id', '', [
    'expires'  => $past,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
setcookie('remember_token', '', [
    'expires'  => $past,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Session zerstören
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: index.php');
exit;
