/*
----------
profile.js
----------
Loads and saves dietary profile data.

NOTE:
- lib/load_profile.php
- lib/save_profile.php
*/

// Ask server for saved profile
function loadProfile() {
    const message = document.getElementById("profileMessage");

    message.className = "form-message";
    message.textContent = "Loading profile...";

    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = function () {
        try {
            // Convert server response to JS object
            const response = JSON.parse(this.responseText);

            if (response.status !== "success") {
                message.className = "form-message error";
                message.textContent = response.message || "Could not load profile.";
                return;
            }

            const profile = response.profile || {};

            // Fill form fields with saved data
            document.getElementById("dietary_goal").value = profile.dietary_goal || "";
            document.getElementById("calorie_target").value = profile.calorie_target || "";
            document.getElementById("kosher").checked = Number(profile.kosher) === 1;
            document.getElementById("halal").checked = Number(profile.halal) === 1;
            document.getElementById("vegetarian").checked = Number(profile.vegetarian) === 1;
            document.getElementById("vegan").checked = Number(profile.vegan) === 1;
            document.getElementById("allergies").value = profile.allergies || "";

            // Clear message after successful load
            message.className = "form-message";
            message.textContent = "";
        } catch (error) {
            message.className = "form-message error";
            message.textContent = "Invalid response while loading profile.";
        }
    };

    xmlhttp.open("GET", "lib/load_profile.php", true);
    xmlhttp.send();
}

// Save profile changes
function saveProfile() {
    const calorieTarget = document.getElementById("calorie_target").value;
    const message = document.getElementById("profileMessage");

    // Calorie check
    if (calorieTarget !== "") {
        const value = parseInt(calorieTarget);

        if (isNaN(value) || value < 0) {
            alert("Please enter a valid calorie target.");
            return false;
        }
    }

    // Collect form values
    const dietaryGoal = document.getElementById("dietary_goal").value;
    const kosher = document.getElementById("kosher").checked ? 1 : 0;
    const halal = document.getElementById("halal").checked ? 1 : 0;
    const vegetarian = document.getElementById("vegetarian").checked ? 1 : 0;
    const vegan = document.getElementById("vegan").checked ? 1 : 0;
    const allergies = document.getElementById("allergies").value;

    message.className = "form-message";
    message.textContent = "Saving profile...";

    const params =
        "dietary_goal=" + encodeURIComponent(dietaryGoal) +
        "&calorie_target=" + encodeURIComponent(calorieTarget) +
        "&kosher=" + encodeURIComponent(kosher) +
        "&halal=" + encodeURIComponent(halal) +
        "&vegetarian=" + encodeURIComponent(vegetarian) +
        "&vegan=" + encodeURIComponent(vegan) +
        "&allergies=" + encodeURIComponent(allergies);

    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = function () {
        try {
            const response = JSON.parse(this.responseText);

            // Show result message with CSS class
            if (response.status === "success") {
                message.className = "form-message success";
                message.textContent = response.message || "Profile saved successfully.";
            } else {
                message.className = "form-message error";
                message.textContent = response.message || "Could not save profile.";
            }
        } catch (error) {
            message.className = "form-message error";
            message.textContent = "Invalid response while saving profile.";
        }
    };

    xmlhttp.open("POST", "lib/save_profile.php", true);
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);

    // Stop normal form submit
    return false;
}