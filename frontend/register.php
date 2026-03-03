<?php
/*
------------
register.php
------------
Shows the registration form.

When submitted:
1. validate input (PHP side)
2. hash password (so we never send plain text)
3. send request to backend using RabbitMQ
4. backend inserts into DB (users table)

If success:
1. hide the form
2. show the "Registration successful" login link

If RabbitMQ is down:
1. show a friendly message (and log the real error)
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

// variable to track if registration was successful
$success = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Trim and sanitize input
    $firstName = trim($_POST["firstname"] ?? "");
    $lastName = trim($_POST["lastname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirmPassword = trim($_POST["confirmPassword"] ?? "");

    // Basic form validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = "Username must be between 3 and 20 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {

        // Hash here (frontend side). Backend should store it as-is.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Prepare the request to send to RabbitMQ
        $request = array(
            "type" => "register",
            "firstname" => $firstName,
            "lastname" => $lastName,
            "email" => $email,
            "username" => $username,
            "password" => $hashedPassword
        );
        
        // Send the request to RabbitMQ and handle the response
        try {
            $response = sendToRabbitMQ($request);
            
            // Expecting a response like: ["status" => "success"] or ["status" => "error", "message" => "Reason for failure"]
            if (is_array($response) && ($response["status"] ?? "") === "success") {
                $success = true;
                $message = ""; // keep it clean; success message is shown below
            } elseif (is_array($response) && ($response["status"] ?? "") === "error") {
                $message = $response["message"] ?? "Registration failed. Please try again.";
            } else {
                $message = "Unexpected response from server.";
            }

        } catch (Throwable $e) {
            error_log("RabbitMQ error in register.php: " . $e->getMessage());
            $message = "Registration service is unavailable (RabbitMQ may not be ready).";
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
    <script src="js/register.js" defer></script>
</head>
<body>
    <h1>Register</h1>
    <!-- Display any messages (errors or success) to the user -->
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Show the registration form only if registration was not successful -->
    <?php if (!$success): ?>
        <form method="POST" action="register.php" id="registerForm" onsubmit="return validateRegisterForm()">
            
        <!-- For better accessibility, we can wrap inputs in labels -->
            <label>First Name:
                <input type="text" id="firstname" name="firstname" required>
            </label>
            <br><br>
            <label>Last Name:
                <input type="text" id="lastname" name="lastname" required>
            </label>
            <br><br>
            <label>Email:
                <input type="email" id="email" name="email" required>
            </label>
            <br><br>
            <label>Username:
                <input type="text" id="username" name="username" required>
            </label>
            <br><br>
            <label>Password:
                <input type="password" id="password" name="password" required>
            </label>
            <br><br>
            <label>Confirm Password:
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </label>
            <br><br>

            <input type="submit" value="Register">
        </form>

        <!-- Link to login page for users who already have an account -->
        <p>Already have an account? <a href="login.php">Log in here</a>.</p>

    <!-- If registration was successful, we hide the form and show a success message with a link to login -->
    <?php else: ?>
        <p>Registration successful! You can now <a href="login.php">log in</a>.</p>
    <?php endif; ?>

</body>
</html>