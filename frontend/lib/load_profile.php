<?php
/*
----------------
load_profile.php
----------------
Loads the logged-in user's dietary profile through RabbitMQ.
*/

session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

header('Content-Type: application/json');

// Block access if user not logged in
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    sendLogMessage(
        "Load profile failed because user is not logged in.",
        "WARNING",
        "frontend"
    );

    echo json_encode(["status" => "error", "message" => "User is not logged in."]);
    exit();
}

$username = $_SESSION["username"] ?? "";

$request = [
    "type" => "get_profile",
    "username" => $username
];

try {
    $response = sendToRabbitMQ($request);

    if (!is_array($response)) {
        sendLogMessage(
            "Load profile failed because RabbitMQ returned an unexpected response.",
            "ERROR",
            "frontend"
        );

        echo json_encode(["status" => "error", "message" => "Unexpected response from server."]);
        exit();
    }

    if (($response["status"] ?? "") !== "success") {
        sendLogMessage(
            "Load profile failed: " . ($response["message"] ?? "Unknown backend error."),
            "ERROR",
            "frontend"
        );
    }

    // Return backend response
    echo json_encode($response);
} catch (Exception $e) {
    sendLogMessage(
        "RabbitMQ error in load_profile.php: " . $e->getMessage(),
        "ERROR",
        "frontend"
    );

    echo json_encode(["status" => "error", "message" => "Profile service is currently unavailable."]);
}
?>