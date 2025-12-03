<?php
require_once __DIR__ . '/includes/auth.php';
init_auth($pdo); // versucht ggf. Auto-Login
// ggf. danach: require_login() oder require_admin()

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

$id = $_GET['id'] ?? null;
if ($id === null || !ctype_digit($id)) {
    die("Ungültige ID");
}

$stmt = $pdo->prepare("
    SELECT * FROM listings 
    WHERE id = :id AND user_id = :uid
");
$stmt->execute([
    ':id'  => $id,
    ':uid' => $_SESSION['user_id']
]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    die("Listing nicht gefunden oder keine Berechtigung.");
}

$errors  = [];
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

    $imagePath = $listing['image_path'];

    if (!empty($_FILES['image']['name'])) {
        $uploadDir  = __DIR__ . '/uploads/';
        $fileTmp    = $_FILES['image']['tmp_name'];
        $fileName   = basename($_FILES['image']['name']);
        $ext        = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Nur JPG, PNG oder GIF erlaubt.";
        } else {
            $newName   = uniqid('card_', true) . '.' . $ext;
            $targetFs  = $uploadDir . $newName;
            $targetWeb = 'uploads/' . $newName;

            if (!move_uploaded_file($fileTmp, $targetFs)) {
                $errors[] = "Fehler beim Hochladen des neuen Bildes.";
            } else {
                if (!empty($listing['image_path'])) {
                    $oldFs = __DIR__ . '/' . $listing['image_path'];
                    if (file_exists($oldFs)) {
                        @unlink($oldFs);
                    }
                }
                $imagePath = $targetWeb;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE listings
            SET title = :t,
                description = :d,
                card_condition = :c,
                price = :p,
                image_path = :img
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([
            ':t'   => $title,
            ':d'   => $description,
            ':c'   => $condition,
            ':p'   => $price,
            ':img' => $imagePath,
            ':id'  => $listing['id'],
            ':uid' => $_SESSION['user_id']
        ]);

        $success = true;
        $listing['title']         = $title;
        $listing['description']   = $description;
        $listing['card_condition']= $condition;
        $listing['price']         = $price;
        $listing['image_path']    = $imagePath;
    }
}

$pageTitle = 'Listing bearbeiten – Poketrade';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Listing bearbeiten</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        Änderungen gespeichert!
        <a href="card_detail.php?id=<?= $listing['id'] ?>" class="alert-link">Zur Detailseite</a>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3 shadow-sm mb-3">
    <div class="mb-3">
        <label class="form-label">Titel</label>
        <input class="form-control" type="text" name="title"
               value="<?= htmlspecialchars($listing['title']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Beschreibung</label>
        <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($listing['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Zustand</label>
        <select class="form-select" name="condition">
            <option value="mint"   <?= $listing['card_condition']==='mint'   ? 'selected' : '' ?>>Mint</option>
            <option value="good"   <?= $listing['card_condition']==='good'   ? 'selected' : '' ?>>Good</option>
            <option value="played" <?= $listing['card_condition']==='played' ? 'selected' : '' ?>>Played</option>
            <option value="poor"   <?= $listing['card_condition']==='poor'   ? 'selected' : '' ?>>Poor</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Preis (EUR)</label>
        <input class="form-control" type="text" name="price"
               value="<?= htmlspecialchars($listing['price']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Aktuelles Bild</label><br>
        <?php if (!empty($listing['image_path'])): ?>
            <img src="<?= htmlspecialchars($listing['image_path']) ?>" alt="Karte"
                 style="max-width:200px;" class="img-thumbnail">
        <?php else: ?>
            <em>Kein Bild vorhanden.</em>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Neues Bild hochladen (optional)</label>
        <input class="form-control" type="file" name="image">
    </div>

    <button class="btn btn-primary" type="submit">Speichern</button>
</form>

<a class="btn btn-outline-secondary" href="my_listings.php">Zurück zu meinen Listings</a>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
