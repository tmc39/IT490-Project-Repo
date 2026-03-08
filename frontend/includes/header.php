<?php
/*
----------
header.php
----------
Reusable header (navigation bar) used by all pages.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="site-header">
    <div class="container">

        <h1 class="site-title"><a href="/index.php">Guilty Spark</a></h1>

        <nav class="site-nav">

            <a href="/index.php">Home</a>

            <?php if (!empty($_SESSION["loggedIn"])): ?>

                <a href="/frontend/home.php">Dashboard</a>
                <a href="/frontend/logout.php">Logout</a>

                <!-- Bust in Silhouette -->
                <a class="nav-user" href="/frontend/home.php">&#128100; <?php echo htmlspecialchars($_SESSION["username"]); ?></a>

            <?php else: ?>

                <a href="/frontend/login.php">Login</a>
                <a href="/frontend/register.php">Register</a>

            <?php endif; ?>

        </nav>

    </div>
</header>