<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB-Verbindung (gleich wie in register.php)
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

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUser = trim($_POST['email_or_user'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($emailOrUser === '' || $password === '') {
        $errors[] = "Bitte alle Felder ausfüllen.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR username = :id LIMIT 1");
        $stmt->execute([':id' => $emailOrUser]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            $success = true;
        } else {
            $errors[] = "Login fehlgeschlagen.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login – Poketrade</title>
</head>
<body>
<h1>Login</h1>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green;">
        Login erfolgreich! Du bist jetzt als <?= htmlspecialchars($_SESSION['username']) ?> eingeloggt.
        <br>
        <a href="index.php">Weiter zur Startseite</a>
    </div>
<?php else: ?>
    <form method="post">
        <div>
            <label>E-Mail oder Username</label><br>
            <input type="text" name="email_or_user"
                   value="<?= htmlspecialchars($_POST['email_or_user'] ?? '') ?>">
        </div>
        <div>
            <label>Passwort</label><br>
            <input type="password" name="password">
        </div>
        <button type="submit">Login</button>
    </form>
<?php endif; ?>

</body>
</html>
