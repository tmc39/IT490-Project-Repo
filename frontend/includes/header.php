<?php
/*
----------
header.php
----------
Reusable site header and navigation bar.

This file is included at the top of pages so we don't have to repeat the same navigation code everywhere.
*/

// Start a session if one is not already running
// This allows us to check if a user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current page file name (e.g., index.php, login.php)
// This is used to highlight the active link in the navbar
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header class="site-header">
    <div class="container">

        <!-- Site title -->
        <h1 class="site-title"><a href="/index.php">Guilty Spark</a></h1>

        <!-- Navigation menu -->
        <nav class="site-nav">

            <!-- Home link -->
            <a class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="/index.php">Home</a>

            <?php if (!empty($_SESSION["loggedIn"])): ?>

                <div class="nav-dropdown">
                    <!-- Dashboard link (shown when user is logged in) -->
                    <a class="<?= ($currentPage === 'dashboard.php' || $currentPage === 'profile.php') ? 'active' : '' ?>" href="/frontend/dashboard.php">Dashboard &#9662;</a>
                    <div class="dropdown-menu">
                        <!-- Profile link (a dropdown option under dashboard) -->
                        <a class="<?= ($currentPage === 'profile.php') ? 'active' : '' ?>" href="/frontend/profile.php">Profile</a>
                    </div>
                </div>
                
                <!-- Logout link -->
                <a href="/frontend/logout.php">Logout</a>

                <!-- 
                Logged-in username with user icon 
                    NOTE: &#xfe0e (variation selector) renders the previous character (&#128100) as text.
                -->
                <a class="nav-user" href="/frontend/dashboard.php">
                    <span class="nav-icon">&#128100;&#xfe0e;</span>
                    <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </a>

            <?php else: ?>

                <!-- Login link (shown when user is not logged in) -->
                <a class="<?= ($currentPage === 'login.php') ? 'active' : '' ?>" href="/frontend/login.php">Login</a>

                <!-- Register link -->
                <a class="<?= ($currentPage === 'register.php') ? 'active' : '' ?>" href="/frontend/register.php">Register</a>

            <?php endif; ?>

        </nav>

    </div>
</header>