<?php
/*
-------------
dashboard.php
-------------
Shows the dashboard only if the user has a valid session.
*/

session_start();

require_once __DIR__ . '/lib/rabbitMQ_web_client.php';

// If user is not logged in, go to login page
if (empty($_SESSION["loggedIn"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"] ?? "";
$sessionKey = $_SESSION["session_key"] ?? "";

// If session key is missing, log out
if (empty($sessionKey)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ask backend to validate the session
$request = [
    "type" => "validate_session",
    "sessionId" => $sessionKey,
    "username" => $username
];

try {
    $response = sendToRabbitMQ($request);

    if (!is_array($response) || ($response["status"] ?? "") !== "success") {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

} catch (Exception $e) {
    error_log("RabbitMQ error in dashboard.php: " . $e->getMessage());
    session_unset();
    session_destroy();
    header("Location: login.php?msg=" . urlencode("Session check is unavailable."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <section class="card">
        <h2>Dashboard</h2>

        <p>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>
        <p>You are logged in. This is your account dashboard.</p>
        <p><a class="btn" href="/frontend/logout.php">Logout</a></p>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>