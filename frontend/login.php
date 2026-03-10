<?php
/*
---------
login.php
---------
Shows the login form and handles login.
*/

session_start();

require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

$message = "";

// If already logged in, go to dashboard
if (!empty($_SESSION["loggedIn"])) {
    header("Location: dashboard.php");
    exit();
}

// Run only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    // Basic validation
    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = "Username must be between 3 and 20 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        $request = [
            'type' => 'login',
            'username' => $username,
            'password' => $password
        ];

        try {
            $response = sendToRabbitMQ($request);

            if (is_array($response) && isset($response['status'])) {
                if ($response['status'] === 'success') {
                    $_SESSION["loggedIn"] = true;
                    $_SESSION["username"] = $username;
                    $_SESSION["session_key"] = $response['session_key'] ?? '';

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = $response['message'] ?? 'Login failed. Please try again.';
                }
            } else {
                $message = "Unexpected response from server.";
            }
        } catch (Exception $e) {
            $message = "Login service not available.";
            error_log("RabbitMQ error in login.php: " . $e->getMessage());
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

    <link rel="stylesheet" href="/public/css/style.css">
    <script src="js/login.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">
        <h2>Login</h2>

        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm" onsubmit="return validateLoginForm()">
            <label for="username">Username
                <input type="text" id="username" name="username" required>
            </label>

            <label for="password">Password
                <input type="password" id="password" name="password" required>
            </label>

            <input type="submit" value="Login">
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>