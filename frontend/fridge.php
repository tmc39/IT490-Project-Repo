<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fridge Scanner</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../public/css/style.css"> <!-- relative path -->

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
            <br>
            <button type="submit" id="scanButton" class="btn">Scan Fridge</button>
        </form>

        <div id="resultsBox" style="display:none; margin-top: 30px;">
            
            <div class="form-message success" style="display: block; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">Scan Successful!</h3>
                <p id="scanMessage" style="margin-bottom: 0;"></p>
            </div>

            <h4>Detected Ingredients:</h4>
            
            <div id="ingredientCheckboxes" class="checkbox-group"></div>

            <button type="button" id="createRecipeBtn" class="btn btn-secondary">Create Custom Recipe</button>
        </div>

        <div id="customRecipesBox" style="display:none; margin-top: 30px; padding: 20px; border: 2px dashed #ccc; border-radius: 8px;">
            <h3 style="margin-top: 0;">My Custom Recipes</h3>
            
            <ul id="recipeList" style="line-height: 1.8; padding-left: 20px;"></ul>
        </div>
        
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
