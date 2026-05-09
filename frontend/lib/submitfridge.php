<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

// --- DEVELOPER BYPASS ---
// If the database is down, just assign a random Guest name so we can test the APIs!
$username = $_SESSION["username"] ?? "Guest_" . rand(1000, 9999);
$sessionKey = $_SESSION["session_key"] ?? "bypass_testing_key";
// ------------------------

// Since image data is large, it should be sent via POST from your frontend javascript
$base64Image = $_POST['image'] ?? "";

// Cancel if image is missing
if ($base64Image == "") {
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
        echo json_encode(["status" => "error", "message" => "Unreadable response from server."]);
        exit();
    }

    // Return the response as JSON so your frontend JS can render it on the screen
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit();
}
?>
