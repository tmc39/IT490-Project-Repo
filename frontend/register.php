<?php
/*
------------
register.php
------------
Shows the registration form and handles registration.
*/

session_start();

require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["firstname"] ?? "");
    $lastName = trim($_POST["lastname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirmPassword = trim($_POST["confirmPassword"] ?? "");

    if (
        empty($firstName) || empty($lastName) || empty($email) ||
        empty($username) || empty($password) || empty($confirmPassword)
    ) {
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
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $request = [
            "type" => "register",
            "firstname" => $firstName,
            "lastname" => $lastName,
            "email" => $email,
            "username" => $username,
            "password" => $hashedPassword
        ];

        try {
            $response = sendToRabbitMQ($request);

            if (is_array($response) && isset($response["status"])) {
                if ($response["status"] === "success") {
                    $success = true;
                } else {
                    $message = $response["message"] ?? "Registration failed. Please try again.";
                }
            } else {
                $message = "Unexpected response from server.";
            }
        } catch (Throwable $e) {
            error_log("RabbitMQ error in register.php: " . $e->getMessage());
            $message = "Registration service is unavailable.";
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/public/css/style.css">
    <script src="js/register.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">
        <h2>Register</h2>

        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" action="register.php" id="registerForm" onsubmit="return validateRegisterForm()">
                <label for="firstname">First Name
                    <input type="text" id="firstname" name="firstname" required>
                </label>

                <label for="lastname">Last Name
                    <input type="text" id="lastname" name="lastname" required>
                </label>

                <label for="email">Email
                    <input type="email" id="email" name="email" required>
                </label>

                <label for="username">Username
                    <input type="text" id="username" name="username" required>
                </label>

                <label for="password">Password
                    <input type="password" id="password" name="password" required>
                </label>

                <label for="confirmPassword">Confirm Password
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </label>

                <input type="submit" value="Register">
            </form>

            <p>Already have an account? <a href="login.php">Log in here</a>.</p>
        <?php else: ?>
            <p>Registration successful! You can now <a href="login.php">log in</a>.</p>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>