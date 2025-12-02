<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
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
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $condition   = $_POST['condition'] ?? 'good';
    $price       = $_POST['price'] ?? '';

    if ($title === '' || $price === '') {
        $errors[] = "Titel und Preis sind Pflichtfelder.";
    }

    if (!is_numeric($price)) {
        $errors[] = "Preis muss eine Zahl sein.";
    }

    // Bild-Upload (optional, aber empfohlen)
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
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Listing erstellen – Poketrade</title>
</head>
<body>
<h1>Neues Listing erstellen</h1>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green;">
        Listing wurde erstellt!
        <a href="browse.php">Alle Listings ansehen</a>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div>
        <label>Titel</label><br>
        <input type="text" name="title"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
    </div>

    <div>
        <label>Beschreibung</label><br>
        <textarea name="description" rows="4" cols="40"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <div>
        <label>Zustand</label><br>
        <select name="condition">
            <option value="mint">Mint</option>
            <option value="good">Good</option>
            <option value="played">Played</option>
            <option value="poor">Poor</option>
        </select>
    </div>

    <div>
        <label>Preis (EUR)</label><br>
        <input type="text" name="price"
               value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
    </div>

    <div>
        <label>Kartenbild (optional)</label><br>
        <input type="file" name="image">
    </div>

    <button type="submit">Listing erstellen</button>
</form>

</body>
</html>
