<?php
// admin/index.php

// Session & Admin-Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    // Nicht eingeloggt oder kein Admin → zurück zum Login
    header('Location: /Poketrade/login.php');
    exit;
}

// DB-Verbindung laden (PDO erwartet)
require_once __DIR__ . '/../config/db.php';

// Prüfen, ob $pdo existiert
if (!isset($pdo)) {
    die('Datenbankverbindung ($pdo) nicht gefunden. Bitte config/db.php prüfen.');
}

// Daten laden
try {
    // Alle Benutzer
    $stmtUsers = $pdo->query("
        SELECT id, username, email, role, created_at
        FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    // Alle Listings
    $stmtListings = $pdo->query("
        SELECT l.id, l.title, l.card_condition, l.price, l.created_at, u.username
        FROM listings l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
    ");
    $listings = $stmtListings->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Fehler beim Laden der Admin-Daten: ' . htmlspecialchars($e->getMessage()));
}

// Seitentitel für Header
$pageTitle = 'Admin Panel - Poketrade';

// Header einbinden
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Admin Dashboard</h1>
    <span class="badge bg-warning text-dark">Admin</span>
</div>

<!-- Kleine Stat-Karten -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 text-muted mb-1">Benutzer insgesamt</h2>
                <p class="h3 mb-0"><?php echo count($users); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 text-muted mb-1">Listings insgesamt</h2>
                <p class="h3 mb-0"><?php echo count($listings); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs: Benutzer / Listings -->
<ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="users-tab" data-bs-toggle="tab"
                data-bs-target="#users-tab-pane" type="button" role="tab"
                aria-controls="users-tab-pane" aria-selected="true">
            Benutzer
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="listings-tab" data-bs-toggle="tab"
                data-bs-target="#listings-tab-pane" type="button" role="tab"
                aria-controls="listings-tab-pane" aria-selected="false">
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
                    <?php echo count($users); ?> Benutzer
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">user</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
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
                    <?php echo count($listings); ?> Listings
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
                                        <td><?php echo htmlspecialchars($listing['id']); ?></td>
                                        <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                        <td><?php echo htmlspecialchars($listing['username']); ?></td>
                                        <td>
                                            <?php
                                            $condition = $listing['card_condition'];
                                            $badgeClass = 'secondary';
                                            switch ($condition) {
                                                case 'mint':
                                                    $badgeClass = 'success';
                                                    break;
                                                case 'good':
                                                    $badgeClass = 'primary';
                                                    break;
                                                case 'played':
                                                    $badgeClass = 'warning';
                                                    break;
                                                case 'poor':
                                                    $badgeClass = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($condition); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo number_format((float)$listing['price'], 2, ',', '.'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($listing['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="/Poketrade/card_detail.php?id=<?php echo urlencode($listing['id']); ?>"
                                                   class="btn btn-outline-secondary">
                                                    Ansehen
                                                </a>
                                                <!-- Platzhalter für spätere Admin-Moderation -->
                                                <!--
                                                <a href="/Poketrade/edit_listing.php?id=..." class="btn btn-outline-primary">Bearbeiten</a>
                                                <a href="/Poketrade/delete_listing.php?id=..." class="btn btn-outline-danger">Löschen</a>
                                                -->
                                            </div>
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

<?php
require_once __DIR__ . '/../includes/footer.php';
