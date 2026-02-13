<?php
// variable to hold messages to be displayed to the user
$message = "";

// runs only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get the form data
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $confirmPassword = trim($_POST["confirmPassword"] ?? '');

    // validate the form data
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $message = "Please fill in all fields.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = "Username must be between 3 and 20 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        // hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $message = "Registration successful! Your username is: " . htmlspecialchars($username);
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
    <script src="js/register.js"></script>
</head>
<body>
    <h1>Register</h1>

    <!-- Display message if there is one -->
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Registration Form Notes:
        1. I ussually prefer to wrap the label around the input for better accessibility, but you can also use the "for" attribute to link them. 
    -->
    <form method="POST" action="" id="registerForm" onsubmit="return validateRegisterForm()">
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

    <!-- Link to login page -->
    <p>Already have an account? <a href="login.php">Login here</a>.</p>

</body>
</html>