<?php

/*
/   This script is a middle-man between the requests made by the frontend Javascript and the DMZ talking to the API
/   Food Info uses this script
*/

require_once(__DIR__ . '/../integration/logging/logClient.php');
require_once '../frontend/lib/rabbitMQ_web_client_DMZ.php';

$searchQuery = $_GET['ID'];
//$maxResults = $_GET['maxresults'];
//$pageNumber = $_GET['page'];

$type = "food_info";

//sets placeholder values if none are given. For parity with old API scripts.
/*
if(empty($_GET['maxresults'])){
    $maxResults = 10;
}
if(empty($_GET['page'])){
    $pageNumber = 0;
}
*/

//Search Query placeholder. If the user somehow fails to give a requested ID, they get bageled.
if($searchQuery == null){
    sendLogMessage(
        "Food info request missing food ID. Defaulting to 3540.",
        "WARNING",
        "backend-api",
        __FILE__,
        __LINE__
    );
    $searchQuery = "3540";
}

$request = [
    "type" => $type,
    "search" => $searchQuery
    ];
try {
    $response = sendToRabbitMQ($request);

//-----------------------------------------------------------
if ($response == null) {
    sendLogMessage(
        "Food info API returned an empty response for food ID: " . $searchQuery,
        "ERROR",
        "backend-api",
        __FILE__,
        __LINE__
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food info API returned no data."));
    exit();
}

if (json_validate(json_encode($response, JSON_FORCE_OBJECT)) == false) {
    sendLogMessage(
        "Food info API returned invalid JSON for food ID: " . $searchQuery,
        "ERROR",
        "backend-api",
        __FILE__,
        __LINE__
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food info API returned invalid data."));
    exit();
}
//-----------------------------------------------------------

    //returns the results as a json object
    header('Content-Type: application/json');
    echo json_encode($response, JSON_FORCE_OBJECT);
        
    //echo implode("\n", $response);
    exit();

} catch (Exception $e) {
    error_log("RabbitMQ error: " . $e->getMessage());
    session_unset();
    session_destroy();
    echo $e->getMessage();
    exit();
}

?>