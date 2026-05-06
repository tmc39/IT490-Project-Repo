<?php
/*
----------------
save_profile.php
----------------
Saves the logged-in user's dietary profile through RabbitMQ.
*/

session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

header('Content-Type: application/json');

// Block access if user not logged in
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    sendLogMessage(
        "Save profile failed because user is not logged in.",
        "WARNING",
        "frontend"
    );

    echo json_encode(["status" => "error", "message" => "User is not logged in."]);
    exit();
}

$username = $_SESSION["username"] ?? "";

// Read values sent from JS
$dietaryGoal = trim($_POST["dietary_goal"] ?? "");
$calorieTarget = trim($_POST["calorie_target"] ?? "");
$allergies = trim($_POST["allergies"] ?? "");

// Convert checkbox values to 0/1
$kosher = isset($_POST["kosher"]) ? (int)$_POST["kosher"] : 0;
$halal = isset($_POST["halal"]) ? (int)$_POST["halal"] : 0;
$vegetarian = isset($_POST["vegetarian"]) ? (int)$_POST["vegetarian"] : 0;
$vegan = isset($_POST["vegan"]) ? (int)$_POST["vegan"] : 0;

$request = [
    "type" => "save_profile",
    "username" => $username,
    "dietary_goal" => $dietaryGoal,
    "calorie_target" => $calorieTarget,
    "kosher" => $kosher,
    "halal" => $halal,
    "vegetarian" => $vegetarian,
    "vegan" => $vegan,
    "allergies" => $allergies
];

try {
    $response = sendToRabbitMQ($request);

    if (!is_array($response)) {
        sendLogMessage(
            "Save profile failed because RabbitMQ returned an unexpected response.",
            "ERROR",
            "frontend"
        );

        echo json_encode(["status" => "error", "message" => "Unexpected response from server."]);
        exit();
    }

    if (($response["status"] ?? "") !== "success") {
        sendLogMessage(
            "Save profile failed: " . ($response["message"] ?? "Unknown backend error."),
            "ERROR",
            "frontend"
        );
    }

    // Return backend response
    echo json_encode($response);
} catch (Exception $e) {
    sendLogMessage(
        "RabbitMQ error in save_profile.php: " . $e->getMessage(),
        "ERROR",
        "frontend"
    );

    echo json_encode(["status" => "error", "message" => "Profile service is currently unavailable."]);
}
?>