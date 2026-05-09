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

    <script src="js/fridge.js" defer></script>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">
        <h2>Fridge Scanner</h2>
        <p>Upload a picture of your fridge to list items and build custom recipes.</p>

        <form id="fridgeForm">
            <label for="fridgeImage">Select Fridge Image:
                <input type="file" id="fridgeImage" accept="image/*" required>
            </label>
            <br><br>
            <button type="submit" id="scanButton">Scan Fridge</button>
        </form>

        <div id="resultsBox" style="display:none; margin-top:20px; padding:15px; background:#e8f5e9; border-radius:5px; color: black;">
            <h3 style="color: #2e7d32;">Scan Successful!</h3>
            <p id="scanMessage"></p>

            <h4>Detected Ingredients:</h4>
            <div id="ingredientCheckboxes" style="margin-bottom: 15px;"></div>

            <button type="button" id="createRecipeBtn">Create Custom Recipe</button>
        </div>

        <div id="customRecipesBox" style="display:none; margin-top:20px; padding:15px; border: 2px dashed #4caf50; border-radius:5px; color: black;">
            <h3>My Custom Recipes</h3>
            <ul id="recipeList" style="line-height: 1.8;"></ul>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
