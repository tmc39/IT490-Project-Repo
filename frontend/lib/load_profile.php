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
        echo json_encode(["status" => "error", "message" => "Unexpected response from server."]);
        exit();
    }

    // Return backend response
    echo json_encode($response);
} catch (Exception $e) {
    error_log("RabbitMQ error in load_profile.php: " . $e->getMessage());

    echo json_encode(["status" => "error", "message" => "Profile service is currently unavailable."]);
}