<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

//gets the parameters provided to this script
$recipeID = $_GET['recipe'];

//cancels if neccessary data is missing
if($recipeID == ""){
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
    error_log("RabbitMQ error in dashboard.php: " . $e->getMessage());
    session_unset();
    session_destroy();
    echo $e->getMessage();
    exit();
}
?>