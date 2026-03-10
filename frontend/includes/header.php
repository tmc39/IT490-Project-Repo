<?php
/*
----------
header.php
----------
Shared site header.
*/

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current file name so we can highlight the active page
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header class="site-header">
    <div class="container">

        <h1 class="site-title"><a href="/index.php">Guilty Spark</a></h1>

        <nav class="site-nav">
            <a class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="/index.php">Home</a>

            <?php if (!empty($_SESSION["loggedIn"])): ?>
                <a class="<?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>" href="/frontend/dashboard.php">Dashboard</a>
                <a class="<?= ($currentPage === 'profile.php') ? 'active' : '' ?>" href="/frontend/profile.php">Profile</a>
                <a href="/frontend/logout.php">Logout</a>

                <a class="nav-user" href="/frontend/dashboard.php">
                    <span class="nav-icon">&#128100;&#xfe0e;</span>
                    <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </a>
            <?php else: ?>
                <a class="<?= ($currentPage === 'login.php') ? 'active' : '' ?>" href="/frontend/login.php">Login</a>
                <a class="<?= ($currentPage === 'register.php') ? 'active' : '' ?>" href="/frontend/register.php">Register</a>
            <?php endif; ?>
        </nav>

    </div>
</header>