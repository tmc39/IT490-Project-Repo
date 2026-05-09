<?php
// Start the session to manage user sessions
session_start();

// Clear everything in the session to log the user out
session_unset();
session_destroy();

// Send the user back to the login page after logging out
header("Location: login.php");
exit();
?>
