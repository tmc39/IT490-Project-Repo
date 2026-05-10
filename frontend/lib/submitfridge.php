<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

$username = $_SESSION["username"] ?? "Guest_" . rand(1000, 9999);
$sessionKey = $_SESSION["session_key"] ?? "bypass_key";
$base64Image = $_POST['image'] ?? "";

if ($base64Image == "") {
    echo json_encode(["status" => "error", "message" => "Failed to scan: missing image data"]);
    exit();
}

$request = [
    "type" => "fridge_scan",
    "sessionId" => $sessionKey,
    "username" => $username,
    "image" => $base64Image
];

try {
    $response = sendToRabbitMQ($request);

    header('Content-Type: application/json');
    if (!is_array($response)) {
        echo json_encode(["status" => "error", "message" => "Unreadable response from server."]);
    } else {
        echo json_encode($response);
    }
    exit();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit();
}
?>
