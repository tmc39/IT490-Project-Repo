<?php
/*
-----------
profile.php
-----------
Shows the profile page only if the user has a valid session.
*/

session_start();

require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

$message = "";

if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

if (empty($sessionKey)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$sessionRequest = [
    "type" => "validate_session",
    "sessionId" => $sessionKey,
    "username" => $username
];

try {
    $response = sendToRabbitMQ($sessionRequest);

    if (!is_array($response) || ($response["status"] ?? "") !== "success") {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (Exception $e) {
    error_log("RabbitMQ error in profile.php: " . $e->getMessage());
    session_unset();
    session_destroy();
    header("Location: login.php?msg=" . urlencode("Session check is unavailable."));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dietaryGoal = trim($_POST["dietary_goal"] ?? "");
    $calorieTarget = trim($_POST["calorie_target"] ?? "");
    $allergies = trim($_POST["allergies"] ?? "");

    $kosher = isset($_POST["kosher"]) ? 1 : 0;
    $halal = isset($_POST["halal"]) ? 1 : 0;
    $vegetarian = isset($_POST["vegetarian"]) ? 1 : 0;
    $vegan = isset($_POST["vegan"]) ? 1 : 0;

    $profileRequest = [
        "type" => "save_profile",
        "username" => $username,
        "dietary_goal" => $dietaryGoal,
        "calorie_target" => $calorieTarget,
        "kosher" => $kosher,
        "halal" => $halal,
        "vegetarian" => $vegetarian,
        "vegan" => $vegan,
        "allergies" => $allergies,
    ];

    try {
        $response = sendToRabbitMQ($profileRequest);

        if (is_array($response) && ($response["status"] ?? "") === "success") {
            $message = "Profile saved successfully.";
        } elseif (is_array($response) && ($response["status"] ?? "") === "error") {
            $message = "Could not save profile: " . ($response["message"] ?? "Unknown error.");
        } else {
            $message = "Unexpected response from server.";
        }

    } catch (Exception $e) {
        error_log("RabbitMQ error in profile.php: " . $e->getMessage());
        $message = "Profile service is currently unavailable.";
    }
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

    <link rel="stylesheet" href="/public/css/style.css">
    <script src="/frontend/js/profile.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">
        <h2>Dietary Profile</h2>

        <p>Update your dietary preferences. These settings may later be used to filter foods, recipes, or recommendations.</p>

        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form id="profileForm" method="POST" action="profile.php" onsubmit="return validateProfileForm()">

            <div class="form-section">
                <label for="dietary_goal">Dietary Goal
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
                <label for="calorie_target">Daily Calorie Target
                    <input type="number" id="calorie_target" name="calorie_target" min="0" placeholder="Example: 2500">
                </label>
            </div>

            <div class="form-section">
                <p><strong>Dietary Restrictions</strong></p>
                <div class="checkbox-group">
                    <label><input type="checkbox" id="kosher" name="kosher"> Kosher</label>
                    <label><input type="checkbox" id="halal" name="halal"> Halal</label>
                    <label><input type="checkbox" id="vegetarian" name="vegetarian"> Vegetarian</label>
                    <label><input type="checkbox" id="vegan" name="vegan"> Vegan</label>
                </div>
            </div>

            <div class="form-section">
                <label for="allergies">Allergies
                    <input type="text" id="allergies" name="allergies" placeholder="Example: peanuts, shellfish">
                </label>
            </div>

            <div class="button-row">
                <input type="submit" value="Save Profile">
            </div>
        </form>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>