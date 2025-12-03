<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; // stellt $pdo bereit

/**
 * Versucht, Benutzer aus Remember-Me-Cookies automatisch einzuloggen.
 */
function try_auto_login_from_cookie(PDO $pdo): void
{
    if (isset($_SESSION['user_id'])) {
        return; // bereits eingeloggt
    }

    if (empty($_COOKIE['remember_id']) || empty($_COOKIE['remember_token'])) {
        return; // keine Cookies vorhanden
    }

    $userId = $_COOKIE['remember_id'];
    $token  = $_COOKIE['remember_token'];

    if (!ctype_digit($userId)) {
        return;
    }

    $stmt = $pdo->prepare("
        SELECT rt.*, u.username, u.email, u.role
        FROM remember_tokens rt
        JOIN users u ON rt.user_id = u.id
        WHERE rt.user_id = :uid
          AND rt.expires_at > NOW()
        ORDER BY rt.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([':uid' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return;
    }

    if (!password_verify($token, $row['token_hash'])) {
        return;
    }

    // Erfolgreich verifiziert -> Session setzen
    $_SESSION['user_id']   = $row['user_id'];
    $_SESSION['username']  = $row['username'];
    $_SESSION['email']     = $row['email'];
    $_SESSION['role']      = $row['role'];
}

/**
 * Muss am Anfang jeder Seite aufgerufen werden, die Auth braucht.
 */
function init_auth(PDO $pdo): void
{
    try_auto_login_from_cookie($pdo);
}

/**
 * Sicherstellen, dass ein User eingeloggt ist.
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /Poketrade/login.php?error=login_required');
        exit;
    }
}

/**
 * Sicherstellen, dass Admin.
 */
function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: /Poketrade/index.php');
        exit;
    }
}
