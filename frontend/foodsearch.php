<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Search</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Shared site CSS -->
    <link rel="stylesheet" href="/public/css/style.css">

    <!-- Login validation script -->
    <script src="js/foodsearch.js" defer></script>

    <style>
        td{
            border-top: 2px;
            border-bottom: 2px;
            border-left: 0px;
            border-right: 0px;
            border-style: solid;
            border-color: black;
            padding: 5px;
        }
    </style>
</head>

<body>

<!-- Shared site navigation bar -->
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">

        <h2>Food Search</h2>

        <!-- Display message if there is one -->
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form>

            <label for="searchterm">Search:
            <input type="text" id="searchterm" name="searchterm" placeholder="food name" required>
            </label>
            
            <label for="searchnum">Max number results:</label>
            <input type="number" id="searchnum" placeholder="number of results">
            
            <br>
            <input type="button" onclick="return getSearchResults()" value="Search">
        </form>

        <br>

        <p id="resultsCounter"></p>

        <div id="resultsDiv"></div>

    </section>
</main>

<!-- Shared site footer -->
<?php include __DIR__ . '/includes/footer.php'; ?>

</body>