<?php 
/*
---------
login.php
---------
Shows login form.

On submit: validate input and send credentials to backend via RabbitMQ.

If success, then create session, save session key, redirect to home.

If fail, then show error message.

If RabbitMQ/backend down, then show service unavailable message.
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

// If already logged in, redirect to home page
if (!empty($_SESSION["loggedIn"])) {
    header("Location: home.php");
    exit();
}

// runs only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // get the username and password from the form
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    // validate the form data
    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = "Username must be between 3 and 20 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {

        // Build the request we send through RabbitMQ to the backend to check the credentials
        $request = array();
        $request['type'] = 'login';
        $request['username'] = $username;
        $request['password'] = $password;

        // Try to contact the login service through RabbitMQ
        $response = null;

        try {
            // Send the request to the backend through RabbitMQ and wait for the response
            $response = sendToRabbitMQ($request);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the RabbitMQ communication
            $message = "Login service not available (RabbitMQ may not be ready).";
            error_log("RabbitMQ error in login.php: " . $e->getMessage());
        }
        
        // If we got a good response, decide what to do based on the response
        if (is_array($response) && isset($response['status']) && $response['status'] === 'success') {

        // Set session variables to indicate the user is logged in
        // NOTE: RabbitMQ will send it to the DB to confirm if the credentials are correct
        $_SESSION["loggedIn"] = true;
        $_SESSION["username"] = $username;

        // Save the session key if the backend sends one back
        $_SESSION["session_key"] = $response['session_key'] ?? '';

        // Send the user to the home page after successful login
        header("Location: home.php");
        exit();
    }

    // If we got a bad response, display the error message
    if (is_array($response) && isset($response['status']) && $response['status'] === 'error') {
        // If the backend sends an error response, display the error message, otherwise show a generic error message
        $message = $response['message'] ?? 'Login failed. Please try again.';
    } elseif ($response !== null && !is_array($response)) {
        // If we got a response but it's not in the expected format, show a generic error message
        $message = "Unexpected response from server.";
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Link to external JS file -->
    <script src="js/login.js" defer></script>
</head>
<body>
    <h1>Login</h1>

    <!-- Display message if there is one -->
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Login Form Notes:
        1. I ussually prefer to wrap the label around the input for better accessibility, but you can also use the "for" attribute to link them. 
    -->
    <form method="POST" action="login.php" id="loginForm" onsubmit="return validateLoginForm()">
        <label for="username">Username:
        <input type="text" id="username" name="username" required></label>
        <br><br>
        <label for="password">Password:
        <input type="password" id="password" name="password" required></label>
        <br><br>
        <input type="submit" value="Login">
    </form>

    <!-- Link to register page -->
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>

</body>
</html>