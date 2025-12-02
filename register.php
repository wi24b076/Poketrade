<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Eigene DB-Verbindung (unabhängig von anderen Dateien),
 * damit $pdo auf jeden Fall existiert.
 */
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

session_start();

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
        // prüfen, ob Username oder E-Mail schon existiert
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
?>

<?php include 'includes/header.php'; ?>

<h2>Registrieren</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
            <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        Registrierung erfolgreich. <a href="login.php">Jetzt einloggen</a>.
    </div>
<?php endif; ?>

<form method="post" class="mt-3" style="max-width:400px;">
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">E-Mail</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Passwort</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Passwort wiederholen</label>
        <input type="password" name="password2" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Registrieren</button>
</form>

<?php include 'includes/footer.php'; ?>
