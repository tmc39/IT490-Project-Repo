<?php
session_start();

require_once __DIR__ . '/rabbitMQ_web_client.php';

// If user is not logged in, return a failure
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    sendLogMessage(
        "Submit review failed because user is not logged in.",
        "WARNING",
        "frontend"
    );

    echo "Cannot submit review: user is not logged in.";
    exit();
}

//gets the username and session key currently being used
$username = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

//everything following this line only runs if the session is valid--------------------------------------

//gets the parameters provided to this script
$recipeID = $_GET['recipe'] ?? "";
$positive = $_GET['isPositive'] ?? "";
$reviewText = $_GET['text'] ?? "";

//cancels if neccessary data is missing
if($recipeID == "" || $positive == ""|| $reviewText == "" || $username == "" || $sessionKey == ""){
    sendLogMessage(
        "Submit review failed because required data is missing.",
        "WARNING",
        "frontend"
    );

    echo "Failed to submit review: missing required data";
    exit();
}

//prepares the request to post a review, which will then be sent to the RabbitMQ server
$request = [
    "type" => "post_review",
    "sessionId" => $sessionKey,
    "username" => $username,
    "recipe" => $recipeID,
    "positive" => $positive,
    "reviewtext" => $reviewText
];
try {
    $response = sendToRabbitMQ($request);

    if (!is_array($response)){
        sendLogMessage(
            "Submit review failed because RabbitMQ response was unreadable.",
            "ERROR",
            "frontend"
        );

        session_unset();
        session_destroy();
        echo "Error: Unreadable response from server.";
        exit();
    }
    else if(($response["status"] ?? "") !== "success"){
        sendLogMessage(
            "Submit review failed: " . ($response["message"] ?? "Unknown backend error."),
            "ERROR",
            "frontend"
        );

        session_unset();
        session_destroy();
        echo ($response["status"] . ": " . $response["message"]);
        exit();
    }

    echo ($response["status"] . ": " . $response["message"]);
    exit();

} catch (Exception $e) {
    sendLogMessage(
        "RabbitMQ error in submitreview.php: " . $e->getMessage(),
        "ERROR",
        "frontend"
    );

    session_unset();
    session_destroy();
    echo $e->getMessage();
    exit();
}
?>