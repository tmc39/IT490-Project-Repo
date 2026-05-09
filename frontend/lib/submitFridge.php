<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

// If user is not logged in, return a failure
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    sendLogMessage(
        "Fridge scan failed because user is not logged in.",
        "WARNING",
        "frontend",
        __FILE__,
        __LINE__
    );
    echo json_encode(["status" => "error", "message" => "Cannot scan: user is not logged in."]);
    exit();
}

$username = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

// Since image data is large, it should be sent via POST from your frontend javascript
$base64Image = $_POST['image'] ?? "";

// Cancel if image is missing
if ($base64Image == "") {
    sendLogMessage(
        "Fridge scan failed because image data is missing.",
        "WARNING",
        "frontend",
        __FILE__,
        __LINE__
    );
    echo json_encode(["status" => "error", "message" => "Failed to scan: missing image data"]);
    exit();
}

// Prepare the request for RabbitMQ
$request = [
    "type" => "fridge_scan",
    "sessionId" => $sessionKey,
    "username" => $username,
    "image" => $base64Image
];

try {
    $response = sendToRabbitMQ($request);

    if (!is_array($response)) {
        sendLogMessage(
            "Fridge scan failed because RabbitMQ response was unreadable.",
            "ERROR",
            "frontend",
            __FILE__,
            __LINE__
        );
        echo json_encode(["status" => "error", "message" => "Unreadable response from server."]);
        exit();
    }

    // Return the response as JSON so your frontend JS can render it on the screen
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();

} catch (Exception $e) {
    sendLogMessage(
        "RabbitMQ error in submitfridge.php: " . $e->getMessage(),
        "ERROR",
        "frontend",
        __FILE__,
        __LINE__
    );
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit();
}
?>
