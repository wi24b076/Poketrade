<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB-Verbindung
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
    $emailOrUser = trim($_POST['email_or_user'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($emailOrUser === '' || $password === '') {
        $errors[] = "Bitte alle Felder ausfüllen.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE email = :id OR username = :id LIMIT 1"
        );
        $stmt->execute([':id' => $emailOrUser]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userRow && password_verify($password, $userRow['password_hash'])) {
            $_SESSION['user_id']  = $userRow['id'];
            $_SESSION['username'] = $userRow['username'];
            $_SESSION['role']     = $userRow['role'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Login fehlgeschlagen.";
        }
    }
}

$pageTitle = 'Login – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h1 class="mb-4">Login</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="card p-3 shadow-sm">
            <div class="mb-3">
                <label class="form-label">E-Mail oder Username</label>
                <input class="form-control" type="text" name="email_or_user"
                       value="<?= htmlspecialchars($_POST['email_or_user'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Passwort</label>
                <input class="form-control" type="password" name="password">
            </div>
            <button class="btn btn-primary w-100" type="submit">Einloggen</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
