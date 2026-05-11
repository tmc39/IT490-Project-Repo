let recipeCounter = 1;

document.getElementById('fridgeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const fileInput = document.getElementById('fridgeImage');
    const scanButton = document.getElementById('scanButton');
    const file = fileInput.files[0];
    if (!file) return;

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
                document.getElementById('scanMessage').innerText = "Added new items to your inventory!";
                const checkboxDiv = document.getElementById('ingredientCheckboxes');
                
                // get a list of ingredients we already have on the screen
                const existingCheckboxes = document.querySelectorAll('.ingredient-cb');
                const existingItems = Array.from(existingCheckboxes).map(cb => cb.value);

                // add only items not alr there
                data.ingredients.forEach(item => {
                    if (!existingItems.includes(item)) {
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
                    }
                });

                // Clear the file input so the user knows they can upload a new picture
                fileInput.value = '';

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

// make re cipe part
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

    // uncheck boxes after saving
    checkboxes.forEach(cb => cb.checked = false);
});
