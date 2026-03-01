<?php
/*
------------
register.php
------------
Shows registration form.

On submit: validate input, hash password, send to backend via RabbitMQ.

Shows success or error message.

If success, then hide form and show login link.

If error, then keep form so user can try again.
*/

// Start the session to manage user sessions
session_start();

// Include the rabbitMQ_web_client to handle communication with the backend
require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

// variable to hold messages to be displayed to the user
$message = "";
// variable to show if the registration was successful (used to decide if we want to show the form again or not)
$success = false;

// runs only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // get the form data
    // NOTE: Database column names are all lowercase, so we use lowercase keys in the request array to match that convention.
    $firstName = trim($_POST["firstname"] ?? '');
    $lastName = trim($_POST["lastname"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $confirmPassword = trim($_POST["confirmPassword"] ?? '');

    // validate the form data
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $message = "Please fill in all fields.";
    // FILTER_VALIDATE_EMAIL is a built-in PHP filter that validates an email address (e.g., it checks for the presence of an "@" symbol and a valid domain).
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";    
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = "Username must be between 3 and 20 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        // hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Build the request array we want to send through rabbitMQ
        // NOTE: Database column names are all lowercase, so we use lowercase keys in the request array to match that convention.
        $request = [
            'type' => 'register',
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'username' => $username,
            'password' => $hashedPassword // we send the hashed password to the backend for storage.
        ];
        
        // Send the request to the backend through RabbitMQ and handle the response
        try {
            $response = sendToRabbitMQ($request);

                // ['status' => 'success'] OR ['status' => 'error', 'message' => 'display appropriate message']
            if (is_array($response) && ($response['status'] ?? '') === 'success') {
                $message = "Registration successful! Your username is: " . htmlspecialchars($username);
                $success = true; // set success to true to hide the form
            
                // if the response comes back with an error, we display the error message returned by the backend (or a generic message if none is provided).
            } elseif (is_array($response) && ($response['status'] ?? '') === 'error') {
                $message = "Registration failed: " . ($response['message'] ?? 'Unknown error');
               
                // if the response is not in the expected format, we show a generic error message. 
            } else { 
                $message = "Unexpected response from server.";
            }

            // if there is an exception (e.g., RabbitMQ server is not available), we catch it and show a user-friendly message.
        } catch (Throwable $e) {
            $message = "Registration service is currently unavailable (RabbitMQ may not be ready).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Link to external JS file -->
    <script src="js/register.js" defer></script>
</head>
<body>
    <h1>Register</h1>

    <!-- Display message if there is one -->
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (!$success): // only show the form if registration was not successful ?>

    <!-- Registration Form Notes:
        1. I ussually prefer to wrap the label around the input for better accessibility, but you can also use the "for" attribute to link them. 
    -->
    <form method="POST" action="register.php" id="registerForm" onsubmit="return validateRegisterForm()">
        <label for="firstname">First Name:
        <input type="text" id="firstname" name="firstname" required></label>
        <br><br>
        <label for="lastname">Last Name:
        <input type="text" id="lastname" name="lastname" required></label>
        <br><br>
        <label for="email">Email:
        <input type="email" id="email" name="email" required></label>
        <br><br>
        <label for="username">Username:
        <input type="text" id="username" name="username" required></label>
        <br><br>
        <label for="password">Password:
        <input type="password" id="password" name="password" required></label>
        <br><br>
        <label for="confirmPassword">Confirm Password:
        <input type="password" id="confirmPassword" name="confirmPassword" required></label>
        <br><br>
        <input type="submit" value="Register">
    </form>

    <!-- end of success check -->
    <?php endif; ?> 

    <!-- ifregistration was successful, we show a message and a link to the login page. If not, we show a link to the login page in case they already have an account. -->
    <?php if ($success): ?>
        <p>Registration successful! You can now <a href="login.php">log in</a>.</p>
    <?php else: ?>
        <p>Already have an account? <a href="login.php">Log in here</a>.</p>
    <?php endif; ?>
</body>
</html>