let recipeCounter = 1;

document.getElementById('fridgeForm').addEventListener('submit', function(e) {
    // This stops the page from refreshing!
    e.preventDefault();

    const fileInput = document.getElementById('fridgeImage');
    const scanButton = document.getElementById('scanButton');
    const file = fileInput.files[0];
    if (!file) return;

    // Give the user visual feedback that it is working
    scanButton.innerText = "Scanning AI...";
    scanButton.disabled = true;

    const reader = new FileReader();
    reader.onloadend = function() {
        const base64Image = reader.result;

        fetch('lib/submitfridge.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'image=' + encodeURIComponent(base64Image)
        })
        .then(response => response.json())
        .then(data => {
            scanButton.innerText = "Scan Fridge";
            scanButton.disabled = false;

            if (data.status === 'success') {
                document.getElementById('resultsBox').style.display = 'block';
                document.getElementById('scanMessage').innerText = data.message;

                const checkboxDiv = document.getElementById('ingredientCheckboxes');
                checkboxDiv.innerHTML = ''; 

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
                alert('Backend Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            scanButton.innerText = "Scan Fridge";
            scanButton.disabled = false;
        });
    };
    reader.readAsDataURL(file);
});

// Handle custom recipe creation
document.getElementById('createRecipeBtn').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.ingredient-cb:checked');
    const selectedIngredients = Array.from(checkboxes).map(cb => cb.value);

    if (selectedIngredients.length === 0) {
        alert("Please select at least one ingredient to make a recipe.");
        return;
    }

    const recipeName = "Recipe " + recipeCounter;
    recipeCounter++;

    document.getElementById('customRecipesBox').style.display = 'block';
    const recipeList = document.getElementById('recipeList');

    const li = document.createElement('li');
    li.innerHTML = `<strong>${recipeName}:</strong> ${selectedIngredients.join(', ')}`;
    recipeList.appendChild(li);

    // Uncheck boxes after saving
    checkboxes.forEach(cb => cb.checked = false);
});
