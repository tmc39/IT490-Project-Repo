<?php 
// variable to hold messages to be displayed to the user
$message = "";

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
        // if all checks pass, set a success message
        $message = "Login successful! Welcome back, " . htmlspecialchars($username);
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
    <script src="js/login.js"></script>
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
    <form method="POST" action="" id="loginForm" onsubmit="return validateLoginForm()">
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