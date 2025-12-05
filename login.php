<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

init_auth($pdo);

// Wenn schon eingeloggt -> weiterleiten
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUser = trim($_POST['email_or_username'] ?? '');
    $password    = $_POST['password'] ?? '';
    $remember    = isset($_POST['remember_me']);

    if ($emailOrUser === '' || $password === '') {
        $error = 'Bitte alle Felder ausfüllen.';
    } else {
        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE email = :id OR username = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $emailOrUser]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Session setzen
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];

            // Optional: Remember-Me
            if ($remember) {
                // Alte Tokens für diesen User löschen
                $del = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = :uid");
                $del->execute([':uid' => $user['id']]);

                // Neuen Token erzeugen
                $rawToken   = bin2hex(random_bytes(32));
                $tokenHash  = password_hash($rawToken, PASSWORD_DEFAULT);
                $expires    = (new DateTime('+30 days'))->format('Y-m-d H:i:s');

                $ins = $pdo->prepare("
                    INSERT INTO remember_tokens (user_id, token_hash, expires_at)
                    VALUES (:uid, :thash, :exp)
                ");
                $ins->execute([
                    ':uid'   => $user['id'],
                    ':thash' => $tokenHash,
                    ':exp'   => $expires
                ]);

                $cookieExpire = time() + 60 * 60 * 24 * 30; // 30 Tage
                $secure   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

                setcookie('remember_id', (string)$user['id'], [
                    'expires'  => $cookieExpire,
                    'path'     => '/',
                    'secure'   => $secure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                setcookie('remember_token', $rawToken, [
                    'expires'  => $cookieExpire,
                    'path'     => '/',
                    'secure'   => $secure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            header('Location: index.php');
            exit;
        } else {
            $error = 'Login fehlgeschlagen. Bitte Zugangsdaten prüfen.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 text-center mb-4 pokemon-font">Poketrade Login</h1>

                    <?php if (!empty($_GET['error']) && $_GET['error'] === 'login_required'): ?>
                        <div class="alert alert-warning">
                            Bitte logge dich ein, um diese Seite zu sehen.
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="email_or_username" class="form-label">E-Mail oder Benutzername</label>
                            <input type="text" class="form-control" id="email_or_username"
                                   name="email_or_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="password"
                                   name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me">Eingeloggt bleiben</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Einloggen</button>
                    </form>

                    <div class="mt-3 text-center">
                        <small>Noch kein Account? <a href="register.php">Jetzt registrieren</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
