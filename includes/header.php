<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Poketrade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Poketrade</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item"><a class="nav-link" href="browse.php">Browse</a></li>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="my_listings.php">My Listings</a></li>
            <li class="nav-item"><a class="nav-link" href="create_listing.php">Create Listing</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
