<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fridge Scanner</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/public/css/style.css">

    <script src="js/fridgescanner.js" defer></script>

    <style>
        td {
            border-top: 2px solid black;
            border-bottom: 2px solid black;
            border-left: 0px;
            border-right: 0px;
            padding: 5px;
        }
        .upload-area {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">

        <h2>Fridge Scanner</h2>
        <p>Upload a picture of your food to identify it and retrieve nutritional data.</p>

        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form id="fridgeForm">
            <div class="upload-area">
                <label for="fridgeImage">Select Fridge Image:
                    <input type="file" id="fridgeImage" name="fridgeImage" accept="image/png, image/jpeg, image/webp" required>
                </label>
            </div>
            
            <br>
            <input type="button" onclick="return scanFridgeImage()" value="Identify Food">
        </form>

        <br>

        <div id="resultsDiv"></div>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
