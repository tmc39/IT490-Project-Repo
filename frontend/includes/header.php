<?php
/*
----------
header.php
----------
Reusable site header and navigation bar.
*/

// Start a session if one is not already running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current page file name (used to highlight the active nav link)
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header class="site-header">
    <div class="container">

        <h1 class="site-title"><a href="/index.php">Guilty Spark</a></h1>

        <nav class="site-nav">
            <a class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="/index.php">Home</a>

            <?php if (!empty($_SESSION["loggedIn"])): ?>

                <!-- Dashboard dropdown -->
                <div class="nav-dropdown">
                    <a class="<?= ($currentPage === 'dashboard.php' || $currentPage === 'profile.php') ? 'active' : '' ?>" href="/frontend/dashboard.php">Dashboard &#9662;</a>
                    <div class="dropdown-menu">
                        <a class="<?= ($currentPage === 'profile.php') ? 'active' : '' ?>" 
                           href="/frontend/profile.php">
                            Profile
                        </a>
                    </div>
                </div>

                <a href="/frontend/logout.php">Logout</a>

                <a class="nav-user" href="/frontend/dashboard.php"><span class="nav-icon">&#128100;&#xfe0e;</span><?php echo htmlspecialchars($_SESSION["username"]); ?></a>

            <?php else: ?>

                <a class="<?= ($currentPage === 'login.php') ? 'active' : '' ?>" href="/frontend/login.php">Login</a>

                <a class="<?= ($currentPage === 'register.php') ? 'active' : '' ?>" href="/frontend/register.php">Register</a>

            <?php endif; ?>

        </nav>
    </div>
</header>