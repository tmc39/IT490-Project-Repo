<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Info</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Shared site CSS -->
    <link rel="stylesheet" href="/public/css/style.css">

    <script src="js/foodinfo.js" defer></script>
</head>

<body onload="getInfo();">

<!-- Shared site navigation bar -->
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">

        <h2>Food Information</h2>

        <!-- Display message if there is one -->
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div id="info"></div>

    </section>
</main>

<!-- Shared site footer -->
<?php include __DIR__ . '/includes/footer.php'; ?>

</body>