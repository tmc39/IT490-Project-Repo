<?php
/*
---------
index.php
---------
Simple home page for the project.
1) http://localhost
2) http://guiltyspark.com
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guilty Spark</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<?php include __DIR__ . '/frontend/includes/header.php'; ?>

<main class="container">
    <section class="card">
        <h2>Group: Guilty Spark</h2>
        <p>This is the home page for our project.</p>
    </section>
</main>

<?php include __DIR__ . '/frontend/includes/footer.php'; ?>

</body>
</html>