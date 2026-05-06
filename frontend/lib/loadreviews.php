<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

//gets the parameters provided to this script
$recipeID = $_GET['recipe'] ?? null;

//cancels if neccessary data is missing
if($recipeID == null || $recipeID == ""){
    sendLogMessage(
        "Load reviews request failed because recipe ID is missing.",
        "WARNING",
        "frontend"
    );

    echo "Failed to load reviews: missing required data";
    exit();
}

//prepares the request to post a review, which will then be sent to the RabbitMQ server
$request = [
    "type" => "load_reviews",
    "recipe" => $recipeID
];

try {
    $response = sendToRabbitMQ($request);

    if ($response == null || $response === "") {
        sendLogMessage(
            "Load reviews request returned an empty response from RabbitMQ.",
            "ERROR",
            "frontend"
        );

        echo "Failed to load reviews: empty response from server.";
        exit();
    }

    /*
    if (!is_array($response)){
        session_unset();
        session_destroy();
        echo "Error: Unreadable response from server.";
        exit();
    }
    else if(($response["status"] ?? "") !== "success"){
        //runs if the server's response status is not "success"
        session_unset();
        session_destroy();
        echo ($response["status"] . ": " . $response["message"]);
        exit();
    }
    */

    //$jsonresponse = json_encode($response);

    //returns the results as a json object
    header('Content-Type: application/json');
    echo $response;
    exit();

} catch (Exception $e) {
    sendLogMessage(
        "RabbitMQ error in loadreviews.php: " . $e->getMessage(),
        "ERROR",
        "frontend"
    );

    session_unset();
    session_destroy();
    echo $e->getMessage();
    exit();
}
?>