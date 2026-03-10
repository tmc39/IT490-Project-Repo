/*
-----------
profile.js
-----------
Handles basic validation for the profile form.
*/

function validateProfileForm() {

    // Get calorie target field
    const calorieTarget = document.getElementById("calorie_target");

    // If calories were entered, validate them
    if (calorieTarget.value !== "") {

        const value = parseInt(calorieTarget.value);

        // Check for invalid or negative values
        if (isNaN(value) || value < 0) {
            alert("Please enter a valid calorie target.");
            return false;
        }
    }

    // Form is valid
    return true;
}