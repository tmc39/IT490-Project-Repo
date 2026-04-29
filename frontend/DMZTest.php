<?php

require_once './lib/rabbitMQ_web_client.php';

// For testing. Put the word you want to search in the URL parameters (i.e. localhost/backend/APITest.php?search=bagel)
$searchQuery = $_GET['search'];
$maxResults = $_GET['maxresults'];
$pageNumber = $_GET['page'];
$type = $_GET['type'];

//sets placeholder values if none are given. For parity with old API scripts.
if($maxresults == null){
    $maxresults = 10;
}
if($page == null){
    $page = 0;
}

if($type == "recipe_search" || $type == "food_search"){
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

}
else if($type == "recipe_info" || $type == "food_info"){
    //Search Query placeholder. If the user somehow fails to give a requested ID, they get bageled.
    if($searchQuery == null){
        $searchQuery = "3540";
    }

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