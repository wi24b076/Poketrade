<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB + Auth laden
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Auto-Login aus Remember-Me-Cookies
init_auth($pdo);

// Favoriten des eingeloggten Users vorladen
$favIds = [];
if (!empty($_SESSION['user_id'])) {
    $stmtFav = $pdo->prepare("
        SELECT listing_id
        FROM favorites
        WHERE user_id = :uid
    ");
    $stmtFav->execute([':uid' => (int)$_SESSION['user_id']]);
    $favIds = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

// Seitentitel für Header
$pageTitle = 'Alle Listings – Poketrade';

// Filter aus GET lesen
$search    = $_GET['q']          ?? '';
$condition = $_GET['condition']  ?? '';
$minPrice  = $_GET['min_price']  ?? '';
$maxPrice  = $_GET['max_price']  ?? '';

// Dynamische WHERE-Bedingungen vorbereiten
$where  = [];
$params = [];

// Suchtext
if ($search !== '') {
    $where[] = '(l.title LIKE :search OR l.description LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

// Zustand
$validConditions = ['mint', 'good', 'played', 'poor'];
if ($condition !== '' && in_array($condition, $validConditions, true)) {
    $where[] = 'l.card_condition = :cond';
    $params[':cond'] = $condition;
}

// Preis von
if ($minPrice !== '' && is_numeric($minPrice)) {
    $where[] = 'l.price >= :minPrice';
    $params[':minPrice'] = (float)$minPrice;
}

// Preis bis
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $where[] = 'l.price <= :maxPrice';
    $params[':maxPrice'] = (float)$maxPrice;
}

$sql = "
    SELECT l.*, u.username
    FROM listings l
    JOIN users u ON l.user_id = u.id
";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY l.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header einbinden (macht HTML <head>, Navbar, etc.)
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Alle Listings</h1>

    <form method="get" class="row g-2 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label" for="q">Suche</label>
            <input type="text"
                   class="form-control"
                   id="q"
                   name="q"
                   placeholder="Titel oder Beschreibung"
                   value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label" for="condition">Zustand</label>
            <select class="form-select" id="condition" name="condition">
                <option value="">Alle</option>
                <option value="mint"   <?= $condition === 'mint'   ? 'selected' : '' ?>>Mint</option>
                <option value="good"   <?= $condition === 'good'   ? 'selected' : '' ?>>Good</option>
                <option value="played" <?= $condition === 'played' ? 'selected' : '' ?>>Played</option>
                <option value="poor"   <?= $condition === 'poor'   ? 'selected' : '' ?>>Poor</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label" for="min_price">Preis von</label>
            <input type="number" step="0.01" min="0"
                   class="form-control"
                   id="min_price"
                   name="min_price"
                   value="<?= htmlspecialchars($minPrice) ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label" for="max_price">Preis bis</label>
            <input type="number" step="0.01" min="0"
                   class="form-control"
                   id="max_price"
                   name="max_price"
                   value="<?= htmlspecialchars($maxPrice) ?>">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter anwenden</button>
        </div>
    </form>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info">Keine Listings mit diesen Filtern gefunden.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($listings as $listing): ?>
                <?php
                $isLoggedIn = !empty($_SESSION['user_id']);
                $isOwner    = $isLoggedIn && ((int)$_SESSION['user_id'] === (int)$listing['user_id']);
                $isFav      = $isLoggedIn && in_array($listing['id'], $favIds);
                ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($listing['image_path'])): ?>
                            <img src="<?= htmlspecialchars($listing['image_path']) ?>"
                                 class="card-img-top"
                                 alt="Karte">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="card_detail.php?id=<?= $listing['id'] ?>">
                                    <?= htmlspecialchars($listing['title']) ?>
                                </a>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                von <?= htmlspecialchars($listing['username']) ?>
                            </h6>
                            <p class="card-text mb-1">
                                Zustand: <?= htmlspecialchars($listing['card_condition']) ?>
                            </p>
                            <p class="card-text fw-bold">
                                Preis: <?= htmlspecialchars($listing['price']) ?> €
                            </p>
                            <p class="card-text small">
                                <?= nl2br(htmlspecialchars($listing['description'])) ?>
                            </p>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <a href="card_detail.php?id=<?= $listing['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    Details
                                </a>

                                <?php if ($isLoggedIn && !$isOwner): ?>
                                    <form method="post"
                                          action="/Poketrade/favorite_toggle.php"
                                          class="m-0">
                                        <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                                        <input type="hidden" name="redirect" value="/Poketrade/browse.php">
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-warning">
                                            <?= $isFav ? '★' : '☆' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
