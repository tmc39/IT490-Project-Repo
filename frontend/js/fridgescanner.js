// Keep track of how many recipes the user has made
let recipeCounter = 1;

// 1. Handle the Image Upload
document.getElementById('fridgeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const fileInput = document.getElementById('fridgeImage');
    const file = fileInput.files[0];
    if (!file) return;

    // Convert image to Base64
    const reader = new FileReader();
    reader.onloadend = function() {
        const base64Image = reader.result;

        // Send to PHP middleman
        fetch('lib/submitfridge.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'image=' + encodeURIComponent(base64Image)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show the results box
                document.getElementById('resultsBox').style.display = 'block';
                document.getElementById('scanMessage').innerText = data.message;

                const checkboxDiv = document.getElementById('ingredientCheckboxes');
                checkboxDiv.innerHTML = ''; // Clear previous scans

                // Deliverable #6: List things inside the fridge via checkboxes
                data.ingredients.forEach(item => {
                    const label = document.createElement('label');
                    label.style.display = 'inline-block';
                    label.style.marginRight = '15px';
                    label.style.cursor = 'pointer';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = item;
                    checkbox.className = 'ingredient-cb';

                    label.appendChild(checkbox);
                    label.appendChild(document.createTextNode(' ' + item));
                    checkboxDiv.appendChild(label);
                });
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Fetch Error:', error));
    };
    reader.readAsDataURL(file);
});

// 2. Handle Custom Recipe Creation (Deliverable #5)
document.getElementById('createRecipeBtn').addEventListener('click', function() {
    // Find all checked boxes
    const checkboxes = document.querySelectorAll('.ingredient-cb:checked');
    const selectedIngredients = Array.from(checkboxes).map(cb => cb.value);

    // Ensure they picked something
    if (selectedIngredients.length === 0) {
        alert("Please select at least one ingredient to make a recipe.");
        return;
    }

    // Auto-name the recipe
    const recipeName = "Recipe " + recipeCounter;
    recipeCounter++;

    // Display the Custom Recipes box
    document.getElementById('customRecipesBox').style.display = 'block';
    const recipeList = document.getElementById('recipeList');

    // Add the new recipe to the screen
    const li = document.createElement('li');
    li.innerHTML = `<strong>${recipeName}:</strong> ${selectedIngredients.join(', ')}`;
    recipeList.appendChild(li);

    // Uncheck boxes so they can make another recipe
    checkboxes.forEach(cb => cb.checked = false);
});
