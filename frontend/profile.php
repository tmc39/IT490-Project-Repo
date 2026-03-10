<?php
/* 
-----------
profile.php
-----------
This page should only show if the login session key is still valid.

1) Grab session_key from PHP session
2) Ask backend to validate it through RabbitMQ
3) If invalid session (or we can’t check), log out and go back to login
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session to manage user sessions
session_start();

// Include the rabbitMQ_web_client to handle communication with the backend
require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

// If we don’t even have login info, go to login.php
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Grab the username and session key from the session
$username   = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

// If session key is missing, treat it like not logged in
if (empty($sessionKey)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ask backend to validate the session key
$request = array();
$request["type"] = "validate_session";
$request["sessionId"] = $sessionKey;
$request["username"] = $username; // Also send username too (confirm the session belongs to the same user)

// Try to contact the validation service through RabbitMQ
try {
    $response = sendToRabbitMQ($request);

    // If backend didn’t confirm success, log out
    if (!is_array($response) || ($response["status"] ?? "") !== "success") {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (Exception $e) {
    // If we can't validate, destroy the session and go back to login with a message
    error_log("RabbitMQ error in profile.php: " . $e->getMessage());
    session_unset();
    session_destroy();

    // Add a simple message on the login page (via query string)
    header("Location: login.php?msg=" . urlencode("Session check is unavailable (RabbitMQ may not be ready)."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Shared site CSS -->
    <link rel="stylesheet" href="/public/css/style.css">

    <!-- Profile validation script -->
    <script src="/frontend/js/profile.js" defer></script>
</head>
<body>

<!-- Shared site navigation bar -->
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">

        <h2>Dietary Profile</h2>

        <p>Update your dietary preferences. These settings may later be used to filter foods, recipes, or recommendations.</p>

        <!-- Profile form -->
        <form id="profileForm" method="POST" action="profile.php" onsubmit="return validateProfileForm()">

        <div class="form-section">
            <!-- Goal -->
            <label>Dietary Goal
                <select id="dietary_goal" name="dietary_goal">
                    <option value="">Select a goal</option>
                    <option value="weight_loss">Weight Loss</option>
                    <option value="calorie_deficit">Calorie Deficit</option>
                    <option value="maintain">Maintain Weight</option>
                    <option value="muscle_gain">Muscle Gain</option>
                </select>
            </label>
        </div>    

        <div class="form-section">    
            <!-- Calories -->
            <label>Daily Calorie Target
                <input type="number" id="calorie_target" name="calorie_target" min="0" placeholder="Example: 2000">
            </label>
        </div>

        <div class="form-section">
            <!-- Restrictions below -->
            <p><strong>Dietary Restrictions</strong></p>

            <div class="checkbox-group">
                <label><input type="checkbox" id="kosher" name="kosher">Kosher</label>
                <label><input type="checkbox" id="halal" name="halal">Halal</label>
                <label><input type="checkbox" id="vegetarian" name="vegetarian">Vegetarian</label>
                <label><input type="checkbox" id="vegan" name="vegan">Vegan</label>
            </div>
        </div>

        <div class="form-section">
            <!-- Allergies -->
            <label>Allergies
                <input type="text" id="allergies" name="allergies" placeholder="Example: peanuts, shellfish">
            </label>
        </div>

            <div class="button-row">
                <input type="submit" value="Save Profile">
            </div>
        </form>

    </section>
</main>

<!-- Shared site footer -->
<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>