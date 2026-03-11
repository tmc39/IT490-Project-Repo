<?php
/*
-------------
dashboard.php
-------------
Shows the dashboard only if the user has a valid session.
*/

session_start();

require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

// If user is not logged in, go to login page
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

// If session key is missing, log out
if (empty($sessionKey)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ask backend to validate the session
$request = [
    "type" => "validate_session",
    "sessionId" => $sessionKey,
    "username" => $username
];

try {
    $response = sendToRabbitMQ($request);

    if (!is_array($response) || ($response["status"] ?? "") !== "success") {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (Exception $e) {
    error_log("RabbitMQ error in dashboard.php: " . $e->getMessage());
    session_unset();
    session_destroy();
    header("Location: login.php?msg=" . urlencode("Session check is unavailable."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Info</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Shared site CSS -->
    <link rel="stylesheet" href="/public/css/style.css">

    <script src="js/recipeinfo.js" defer></script>
</head>

<body onload="getInfo();">

<!-- Shared site navigation bar -->
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <!-- Display recipe information here -->
    <section class="card">

        <h1>Recipe Information</h1>

        <!-- Display message if there is one -->
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div id="info"></div>

    </section>

    <br></br>

    <!-- section where user writes new reviews -->
    <section class="card">
        <h1>Write a Review</h1>

        <label for="newReviewType">Do you reccomend this recipe?</label>
        <select id="newReviewType" name="newReviewType">
            <option value="positive">Reccomend this recipe</option>
            <option value="negative">Don't recommend this recipe</option>
        </select>

        <br></br>

        <label for="newReviewContent">Provide details here:</label>
        <textarea rows="8" cols="100" id="newReviewContent" name="newReviewContent" placeholder="Maximum 450 characters." maxlength="450"></textarea>

        <br></br>

        <input type="button" onclick="return postReview()" value="Submit Review">
        
        <br></br>

        <p id="postReviewResult"></p>
    </section>

    <br></br>

    <!-- where user reviews for a recipe will show -->
    <section class="card">
        <h1>User Reviews</h1>

        <div id="reviewslist">
            <div style="border-style: solid;">
                <h2>Testman</h2>
                <h4 style="background-color: lime;">Recommends this recipe</h4>
                <p>This bagel fucking sucks.</p>
            </div>
        </div>
    </section>
</main>

<!-- Shared site footer -->
<?php include __DIR__ . '/includes/footer.php'; ?>

</body>