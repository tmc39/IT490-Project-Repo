<?php

/*
/   This script is a middle-man between the requests made by the frontend Javascript and the DMZ talking to the API
/   Recipe Search uses this script
*/

require_once '../frontend/lib/rabbitMQ_web_client_DMZ.php';

$searchQuery = $_GET['search'];
$maxResults = $_GET['results'];
$pageNumber = $_GET['page'];

$type = "recipe_search";

//sets placeholder values if none are given. For parity with old API scripts.
if(empty($_GET['results'])){
    $maxResults = 10;
}
if(empty($_GET['page'])){
    $pageNumber = 0;
}

//Search Query placeholder. If the user somehow fails to give a requested name, they get bageled.
    if($searchQuery == null){
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