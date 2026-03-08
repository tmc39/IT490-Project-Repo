<?php
/*
----------
header.php
----------
Reusable header (navigation bar) used by all pages.
*/

// Start session if there isn't one already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Grab current file name
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header class="site-header">
    <div class="container">

        <h1 class="site-title"><a href="/index.php">Guilty Spark</a></h1>

        <nav class="site-nav">

            <a class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="/index.php">Home</a>

            <?php if (!empty($_SESSION["loggedIn"])): ?>

                <a class="<?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>" href="/frontend/dashboard.php">Dashboard</a>
                <a href="/frontend/logout.php">Logout</a>

                <!-- Bust in Silhouette -->
                <a class="nav-user" href="/frontend/dashboard.php">&#128100;&#xfe0e; <?php echo htmlspecialchars($_SESSION["username"]); ?></a>

            <?php else: ?>

                <a class="<?= ($currentPage === 'login.php') ? 'active' : '' ?>" href="/frontend/login.php">Login</a>
                <a class="<?= ($currentPage === 'register.php') ? 'active' : '' ?>" href="/frontend/register.php">Register</a>

            <?php endif; ?>

        </nav>

    </div>
</header>