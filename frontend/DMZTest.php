<?php

require_once './lib/rabbitMQ_web_client.php';

// For testing. Put the word you want to search in the URL parameters (i.e. localhost/backend/APITest.php?search=bagel)
$searchQuery = $_GET['search'];
$maxResults = $_GET['maxresults'];
$pageNumber = $_GET['page'];
$type = $_GET['type'];

if($type == "recipe_search" || $type == "food_search"){
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

}
else if($type == "recipe_info" || $type == "food_info"){
    $request = [
    "type" => $type,
    "search" => $searchQuery
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

}

?>