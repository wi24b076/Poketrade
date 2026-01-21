<?php
// admin/index.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Auth-System einbinden (inkl. DB $pdo)
require_once __DIR__ . '/../includes/auth.php';

// Auto-Login über Remember-Me-Cookies versuchen
init_auth($pdo);

// Nur Admins dürfen hier rein
require_admin();

// Daten aus der DB laden
try {
    // Alle Benutzer
    $stmtUsers = $pdo->query("
        SELECT id, username, email, role, created_at, is_blocked
        FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    // Alle Listings
    $stmtListings = $pdo->query("
        SELECT l.id, l.title, l.card_condition, l.price, l.created_at, u.username, l.is_blocked
        FROM listings l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
    ");
    $listings = $stmtListings->fetchAll(PDO::FETCH_ASSOC);

    // Statistiken
    $stmtStats = $pdo->query("SELECT COUNT(*) AS user_count FROM users");
    $userCount = $stmtStats->fetchColumn();

    $stmtStats = $pdo->query("SELECT COUNT(*) AS listing_count FROM listings");
    $listingCount = $stmtStats->fetchColumn();

    $stmtStats = $pdo->query("SELECT COUNT(*) AS blocked_users FROM users WHERE is_blocked = 1");
    $blockedUserCount = $stmtStats->fetchColumn();

    $stmtStats = $pdo->query("SELECT COUNT(*) AS blocked_listings FROM listings WHERE is_blocked = 1");
    $blockedListingCount = $stmtStats->fetchColumn();

} catch (PDOException $e) {
    die('Fehler beim Laden der Admin-Daten: ' . htmlspecialchars($e->getMessage()));
}

// Seitentitel für Header
$pageTitle = 'Admin Panel - Poketrade';

// Header einbinden
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Admin Dashboard</h1>
        <span class="badge bg-warning text-dark">Admin</span>
    </div>

    <!-- Stat-Karten -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-1">Benutzer insgesamt</h2>
                    <p class="h3 mb-0"><?= $userCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-1">Listings insgesamt</h2>
                    <p class="h3 mb-0"><?= $listingCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-1">Gesperrte Benutzer</h2>
                    <p class="h3 mb-0"><?= $blockedUserCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-1">Gesperrte Listings</h2>
                    <p class="h3 mb-0"><?= $blockedListingCount ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Benutzer / Listings -->
    <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-tab-pane"
                type="button" role="tab" aria-controls="users-tab-pane" aria-selected="true">
                Benutzer
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listings-tab" data-bs-toggle="tab" data-bs-target="#listings-tab-pane"
                type="button" role="tab" aria-controls="listings-tab-pane" aria-selected="false">
                Listings
            </button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        <!-- Benutzer-Tabelle -->
        <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Benutzerübersicht</h2>
                    <span class="badge bg-secondary">
                        <?= count($users); ?> Benutzer
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($users)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Rolle</th>
                                        <th>Registriert am</th>
                                        <th>Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <?= $user['role'] === 'admin' ? 
                                                    '<span class="badge bg-danger">Admin</span>' : 
                                                    '<span class="badge bg-primary">User</span>' ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                                            <td>
                                                <form method="post" action="user_toggle_block.php" style="display:inline">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <?= $user['is_blocked'] ? 'Entsperren' : 'Sperren' ?>
                                                    </button>
                                                </form>
                                                <form method="post" action="user_delete.php" style="display:inline" onsubmit="return confirm('Benutzer wirklich löschen?');">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Löschen</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="p-3 mb-0">Keine Benutzer gefunden.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Listings-Tabelle -->
        <div class="tab-pane fade" id="listings-tab-pane" role="tabpanel" aria-labelledby="listings-tab">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Alle Listings</h2>
                    <span class="badge bg-secondary">
                        <?= count($listings); ?> Listings
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($listings)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Titel</th>
                                        <th>Owner</th>
                                        <th>Zustand</th>
                                        <th>Preis (€)</th>
                                        <th>Erstellt am</th>
                                        <th>Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listings as $listing): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($listing['id']) ?></td>
                                            <td><?= htmlspecialchars($listing['title']) ?></td>
                                            <td><?= htmlspecialchars($listing['username']) ?></td>
                                            <td><?= htmlspecialchars($listing['card_condition']) ?></td>
                                            <td><?= number_format((float) $listing['price'], 2, ',', '.') ?> €</td>
                                            <td><?= htmlspecialchars($listing['created_at']) ?></td>
                                            <td>
                                                <form method="post" action="listing_toggle_block.php" style="display:inline">
                                                    <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <?= $listing['is_blocked'] ? 'Entsperren' : 'Sperren' ?>
                                                    </button>
                                                </form>
                                                <form method="post" action="listing_delete.php" style="display:inline" onsubmit="return confirm('Listing wirklich löschen?');">
                                                    <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Löschen</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="p-3 mb-0">Keine Listings gefunden.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
