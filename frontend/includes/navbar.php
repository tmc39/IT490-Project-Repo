<?php
/*
----------
navbar.php
----------
Reusable navigation bar used by all pages (I got tired of having to edit the navbar for each page individually).

NOTE: session_start() must already be called.
*/
?>

<header class="site-header">
    <div class="container">

        <h1 class="site-title">
            <a href="/index.php">Guilty Spark</a>
        </h1>

        <nav class="site-nav">
            <a href="/index.php">Home</a>

            <?php if (!empty($_SESSION["loggedIn"])): ?>
                <a href="/frontend/home.php">Dashboard</a>
                <a href="/frontend/logout.php">Logout</a>
            <?php else: ?>
                <a href="/frontend/login.php">Login</a>
                <a href="/frontend/register.php">Register</a>
            <?php endif; ?>

        </nav>

    </div>
</header>