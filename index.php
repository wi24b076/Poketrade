<?php include 'includes/header.php'; ?>

<h1>Willkommen bei Poketrade</h1>

<?php if (isset($_SESSION['username'])): ?>
    <p>Angemeldet als <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
<?php else: ?>
    <p>Du bist nicht eingeloggt.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
