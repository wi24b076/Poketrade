<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $condition   = $_POST['condition'] ?? 'good';
    $price       = $_POST['price'] ?? '';

    if ($title === '' || $price === '') {
        $errors[] = "Titel und Preis sind Pflichtfelder.";
    }
    if ($price !== '' && !is_numeric($price)) {
        $errors[] = "Preis muss eine Zahl sein.";
    }

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir  = __DIR__ . '/uploads/';
        $fileTmp    = $_FILES['image']['tmp_name'];
        $fileName   = basename($_FILES['image']['name']);
        $ext        = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Nur JPG, PNG oder GIF erlaubt.";
        } else {
            $newName   = uniqid('card_', true) . '.' . $ext;
            $targetFs  = $uploadDir . $newName;
            $targetWeb = 'uploads/' . $newName;

            if (!move_uploaded_file($fileTmp, $targetFs)) {
                $errors[] = "Fehler beim Hochladen des Bildes.";
            } else {
                $imagePath = $targetWeb;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO listings (user_id, title, description, card_condition, price, image_path)
            VALUES (:uid, :t, :d, :c, :p, :img)
        ");
        $stmt->execute([
            ':uid' => $_SESSION['user_id'],
            ':t'   => $title,
            ':d'   => $description,
            ':c'   => $condition,
            ':p'   => $price,
            ':img' => $imagePath
        ]);
        $success = true;
        // Formular leeren
        $_POST = [];
    }
}

$pageTitle = 'Listing erstellen – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Neues Listing erstellen</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        Listing wurde erstellt!
        <a href="browse.php" class="alert-link">Alle Listings ansehen</a>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3 shadow-sm">
    <div class="mb-3">
        <label class="form-label">Titel</label>
        <input class="form-control" type="text" name="title"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Beschreibung</label>
        <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Zustand</label>
        <select class="form-select" name="condition">
            <option value="mint"   <?= (($_POST['condition'] ?? 'good') === 'mint')   ? 'selected' : '' ?>>Mint</option>
            <option value="good"   <?= (($_POST['condition'] ?? 'good') === 'good')   ? 'selected' : '' ?>>Good</option>
            <option value="played" <?= (($_POST['condition'] ?? 'good') === 'played') ? 'selected' : '' ?>>Played</option>
            <option value="poor"   <?= (($_POST['condition'] ?? 'good') === 'poor')   ? 'selected' : '' ?>>Poor</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Preis (EUR)</label>
        <input class="form-control" type="text" name="price"
               value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Kartenbild (optional)</label>
        <input class="form-control" type="file" name="image">
    </div>

    <button class="btn btn-success" type="submit">Listing erstellen</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
