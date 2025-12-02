<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// eigene DB-Verbindung
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

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "Bitte alle Felder ausfüllen.";
    }
    if ($password !== $password2) {
        $errors[] = "Passwörter stimmen nicht überein.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $stmt->execute([':u' => $username, ':e' => $email]);

        if ($stmt->fetch()) {
            $errors[] = "Username oder E-Mail ist bereits vergeben.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role)
                VALUES (:u, :e, :p, 'user')
            ");
            $insert->execute([
                ':u' => $username,
                ':e' => $email,
                ':p' => $hash
            ]);

            $success = true;
        }
    }
}

$pageTitle = 'Registrieren – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">Registrieren</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?>
                    <div><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Registrierung erfolgreich. <a href="login.php" class="alert-link">Jetzt einloggen</a>.
            </div>
        <?php endif; ?>

        <form method="post" class="card p-3 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" type="text" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">E-Mail</label>
                <input class="form-control" type="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Passwort</label>
                <input class="form-control" type="password" name="password">
            </div>
            <div class="mb-3">
                <label class="form-label">Passwort wiederholen</label>
                <input class="form-control" type="password" name="password2">
            </div>
            <button class="btn btn-success w-100" type="submit">Registrieren</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
