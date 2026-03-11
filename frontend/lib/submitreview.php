<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

// If user is not logged in, return a failure
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    echo "Cannot submit review: user is not logged in.";
    exit();
}

//gets the username and session key currently being used
$username = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

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
        echo "Error: session not validated.";
        exit();
    }

} catch (Exception $e) {
    error_log("RabbitMQ error in dashboard.php: " . $e->getMessage());
    session_unset();
    session_destroy();
    echo $e->getMessage();
    exit();
}

echo $username;
?>