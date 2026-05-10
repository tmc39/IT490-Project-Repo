<?php

/*
/   This script is a middle-man between the requests made by the frontend Javascript and the DMZ talking to the API
/   Food Search uses this script
*/

require_once(__DIR__ . '/../integration/logging/logClient.php');
require_once '../frontend/lib/rabbitMQ_web_client_DMZ.php';

$searchQuery = $_GET['search'];
$maxResults = $_GET['results'];
$pageNumber = $_GET['page'];

$type = "food_search";

//sets placeholder values if none are given. For parity with old API scripts.
if(empty($_GET['results'])){
    sendLogMessage(
        "Food search request missing results value. Defaulting to 10.",
        "WARNING",
        "backend-api",
        __FILE__,
        __LINE__
    );
    $maxResults = 10;
}
if(empty($_GET['page'])){
    sendLogMessage(
        "Food search request missing page value. Defaulting to 0.",
        "WARNING",
        "backend-api",
        __FILE__,
        __LINE__
    );
    $pageNumber = 0;
}

//Search Query placeholder. If the user somehow fails to give a requested name, they get bageled.
if($searchQuery == null){
    sendLogMessage(
        "Food search request missing search query. Defaulting to bagel.",
        "WARNING",
        "backend-api",
        __FILE__,
        __LINE__
    );
    $searchQuery = "bagel";
}

$request = [
    "type" => $type,
    "search" => $searchQuery,
    "maxresults" => $maxResults,
    "page" => $pageNumber
    ];
try {
    $response = sendToRabbitMQ($request);

//-----------------------------------------------------------
if ($response == null) {
    sendLogMessage(
        "Food search API returned an empty response for search query: " . $searchQuery,
        "ERROR",
        "backend-api",
        __FILE__,
        __LINE__
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food search API returned no data."));
    exit();
}

if (json_validate(json_encode($response, JSON_FORCE_OBJECT))) {
    sendLogMessage(
        "Food search API returned invalid JSON for search query: " . $searchQuery,
        "ERROR",
        "backend-api",
        __FILE__,
        __LINE__
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food search API returned invalid data."));
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