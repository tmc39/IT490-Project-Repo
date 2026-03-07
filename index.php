<?php
/*
---------
index.php
---------

Simple landing page for project.
1) http://localhost
2) http://guiltyspark.com

*/

// Start the session to manage user sessions
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I don't know what to call this site</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Shared site CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<!-- Shared site navigation bar -->
<?php include __DIR__ . '/frontend/includes/navbar.php'; ?>

<main class="container">
    <section class="card">
        
        <h2>Just testing stuff out</h2>

        <p>Nothing to see here folks!</p>

    </section>
</main>

</body>
</html>