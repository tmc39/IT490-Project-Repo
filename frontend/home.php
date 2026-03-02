<?php
/* 
--------
home.php
--------
This page should only be visible if the user is actually logged in.

1) We gab the session_key we stored after login
2) We ask the backend to confirm it's still valid (validate_session) through RabbitMQ
3) If it's not valid, we log the user out and send them to login.php

NOTE: If RabbitMQ isn't running yet, we show a message but don't crash the page.
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session to manage user sessions
session_start();

// Include the rabbitMQ_web_client to handle communication with the backend
require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

// variable to hold messages to be displayed to the user
$message = "";

// If the user is not logged in, send them to the login page
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username and session key from the session variables
$username = $_SESSION["username"] ?? '';
$session_key = $_SESSION["session_key"] ?? '';

// If we don't have session key, treat. it like "not logged in" and send them to the login page
if (empty($session_key)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ask backend to validate the session key through RabbitMQ
$request = array();
$request['type'] = 'validate_session';
$request['sessionId'] = $session_key;

// Try to contact the session validation service through RabbitMQ and check if the session is valid
try {
    // Send the request to the backend through RabbitMQ and wait for the response
    $response = sendToRabbitMQ($request);

    // If the session is not valid, log the user out and send them to the login page
    if (!is_array($response) || (($response['status'] ?? '') !== 'success')) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (Exception $e) {
    // RabbitMQ might be down, so don't crash the page, but show an error message and log the error for debugging
    $message = "Session check is not available (RabbitMQ may not be ready).";
    error_log("RabbitMQ error in home.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <h1>Home</h1>

    <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>

    <!-- Display message if there is one -->
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <p>This is the home page. You are logged in.</p>

    <!-- Link to logout page -->
    <p><a href="logout.php">Logout</a></p>
</body>
</html>