<?php

/*
/   This script is a middle-man between the requests made by the frontend Javascript and the DMZ talking to the API
/   Fridge Scanner uses this script
*/

require_once(__DIR__ . '/../integration/logging/logClient.php');
require_once '../frontend/lib/rabbitMQ_web_client_DMZ.php';

// Base64 images are too large for GET requests, so we use POST
$base64Image = $_POST['image'] ?? null;
$username = $_POST['username'] ?? "Unknown"; 

$type = "api_fridge_scan";

// Image Query placeholder. If the frontend fails to send an image, log and return error.
if($base64Image == null){
    sendLogMessage(
        "Fridge scan request missing image data.",
        "WARNING",
        "backend-api",
        __FILE__,
        __LINE__
    );
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Missing image data."));
    exit();
}

$request = [
    "type" => $type,
    "image" => $base64Image,
    "username" => $username
    ];

try {
    // Sends directly to the DMZ queue based on your team's rabbitMQ_web_client_DMZ.php config
    $response = sendToRabbitMQ($request);

//-----------------------------------------------------------
if ($response == null) {
    sendLogMessage(
        "Fridge API returned an empty response.",
        "ERROR",
        "backend-api",
        __FILE__,
        __LINE__
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Fridge API returned no data."));
    exit();
}

if (json_validate(json_encode($response, JSON_FORCE_OBJECT)) == false) {
    sendLogMessage(
        "Fridge API returned invalid JSON.",
        "ERROR",
        "backend-api",
        __FILE__,
        __LINE__
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Fridge API returned invalid data."));
    exit();
}
//-----------------------------------------------------------

    //returns the results as a json object
    header('Content-Type: application/json');
    echo json_encode($response, JSON_FORCE_OBJECT);
        
    exit();

} catch (Exception $e) {
    error_log("RabbitMQ error: " . $e->getMessage());
    session_unset();
    session_destroy();
    echo $e->getMessage();
    exit();
}

?>
