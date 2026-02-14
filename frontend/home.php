<?php
// Start the session to manage user sessions
session_start();

// If the user is not logged in, send them to the login page
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("Location: login.php");
    exit();
}

// Get the username from the session to display a welcome message
$username = $_SESSION["username"] ?? "User";
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

    <!-- Link to logout page -->
    <p><a href="logout.php">Logout</a></p>
</body>
</html>