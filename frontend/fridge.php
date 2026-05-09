<main class="container">
    <section class="card">
        <h2>Fridge Scanner</h2>
        <p>Upload a picture of your fridge to list items and build custom recipes.</p>

        <form id="fridgeForm">
            <label for="fridgeImage">Select Fridge Image:</label>
            <input type="file" id="fridgeImage" accept="image/*" required>
            <br><br>
            <button type="submit">Scan Fridge</button>
        </form>

        <div id="resultsBox" style="display:none; margin-top:20px; padding:15px; background:#e8f5e9; border-radius:5px;">
            <h3 style="color: #2e7d32;">Scan Successful!</h3>
            <p id="scanMessage"></p>

            <h4>Detected Ingredients:</h4>
            <div id="ingredientCheckboxes" style="margin-bottom: 15px;"></div>

            <button id="createRecipeBtn">Create Custom Recipe</button>
        </div>

        <div id="customRecipesBox" style="display:none; margin-top:20px; padding:15px; border: 2px dashed #4caf50; border-radius:5px;">
            <h3>My Custom Recipes</h3>
            <ul id="recipeList" style="line-height: 1.8;"></ul>
        </div>
    </section>
</main>
