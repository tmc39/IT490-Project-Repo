<?php
/* 
--------
home.php
--------
This page should only show if the login session key is still valid.

1) Grab session_key from PHP session
2) Ask backend to validate it through RabbitMQ
3) If invalid session (or we can’t check), log out and go back to login
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

// If we don’t even have login info, go to login.php
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Grab the username and session key from the session
$username   = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

// If session key is missing, treat it like not logged in
if (empty($sessionKey)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ask backend to validate the session key
$request = array();
$request["type"] = "validate_session";
$request["sessionId"] = $sessionKey;
$request["username"] = $username; // Also send username too (confirm the session belongs to the same user)

// Try to contact the validation service through RabbitMQ
try {
    $response = sendToRabbitMQ($request);

    // If backend didn’t confirm success, log out
    if (!is_array($response) || ($response["status"] ?? "") !== "success") {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (Exception $e) {
    // If we can't validate, destroy the session and go back to login with a message
    error_log("RabbitMQ error in home.php: " . $e->getMessage());
    session_unset();
    session_destroy();

    // Add a simple message on the login page (via query string)
    header("Location: login.php?msg=" . urlencode("Session check is unavailable (RabbitMQ may not be ready)."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Shared site CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
</head>

<body>

<header class="site-header">
    <div class="container">
        <h1 class="site-title"><a href="/index.php">Guilty Spark</a></h1>

        <nav class="site-nav">
            <a href="/index.php">Home</a>
            <a href="/frontend/home.php">Dashboard</a>
            <a href="/frontend/logout.php">Logout</a>
        </nav>
    </div>
</header>


<main class="container">

<section class="card">

    <h2>Dashboard</h2>

    <p>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>

    <p>This is the home page. You are logged in.</p>

    <p><a class="btn" href="/frontend/logout.php">Logout</a></p>

</section>

</main>

</body>
</html>