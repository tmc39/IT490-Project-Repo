/*
----------
profile.js
----------
*/

function validateProfileForm() {
    const calorieTarget = document.getElementById("calorie_target");

    if (calorieTarget.value !== "") {
        const value = parseInt(calorieTarget.value);

        if (isNaN(value) || value < 0) {
            alert("Please enter a valid calorie target.");
            return false;
        }
    }

    return true;
}