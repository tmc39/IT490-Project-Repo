#!/usr/bin/php
<?php
require_once(__DIR__ . '/../lib/path.inc');
require_once(__DIR__ . '/../lib/get_host_info.inc');
require_once(__DIR__ . '/../lib/rabbitMQLib.inc');
require_once(__DIR__ . '/../../database/mysqlconnect.php');

function doLogin($username, $password)
{
  // Connect to the database
  $db = getDbConnection();

  // If DB is down, we return a clear error message
  if ($db === null) {
      return array("status" => "error", "message" => "Database is not reachable at the moment.");
  }

  // Look up the user in the database
  $stmt = $db->prepare("SELECT password FROM users WHERE username = ?");

  // If prepare fails, $stmt is false, so we can only close $db
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Database query could not be prepared.");
  }
  // Bind the username parameter and execute the query
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  // If user doesn't exist
  if ($result->num_rows === 0) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "User not found.");
  }

  // Fetch the stored password for the user
  $row = $result->fetch_assoc();
  $storedPassword = $row["password"];

  // For now: plain-text compare (you already know this will change later)
  if ($password !== $storedPassword) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Wrong password.");
  }

  // Done with the SELECT statement
  $stmt->close();

  // Create a session key
  $sessionKey = bin2hex(random_bytes(16));

  // Store the session key in the sessions table
  $stmt = $db->prepare("INSERT INTO sessions (session_key, username) VALUES (?, ?)");

  // If prepare fails, $stmt is false, so we can only close $db
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Could not prepare statement to save session key.");
  }

  // Bind the session key and username parameters and execute the query
  $stmt->bind_param("ss", $sessionKey, $username);

  // If execute fails, we return an error message and close the statement and database connection
  if (!$stmt->execute()) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Could not save session key.");
  }

  // Done with the INSERT statement
  $stmt->close();
  $db->close();

  // Send session key back so the frontend can store it in $_SESSION
  return array("status" => "success", "session_key" => $sessionKey);
}

function doValidate($sessionId)
{
  // Connect to the database
  $db = getDbConnection();

  // If DB is down, we return a clear error message
  if ($db === null) {
      return array("status" => "error", "message" => "Database is not reachable at the moment.");
  }

  // Validate the session key
  if (!isset($sessionId) || trim($sessionId) === "") {
      $db->close();
      return array("status" => "error", "message" => "No session key was provided.");
  }

  // Look up the session key in the database
  $stmt = $db->prepare("SELECT session_key FROM sessions WHERE session_key = ?");

  // If prepare fails, $stmt is false, so we can only close $db
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Could not prepare statement to check session key.");
  }

  // Bind the session key parameter and execute the query
  $stmt->bind_param("s", $sessionId);
  $stmt->execute();
  $result = $stmt->get_result();

  // If session key doesn't exist
  if ($result->num_rows === 0) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Session key not valid.");
  }

  // Done with the SELECT statement
  $stmt->close();
  $db->close();

  // If we got here, the session key is valid
  return array("status" => "success", "message" => "Session key is valid.");
}

function requestProcessor($request)
{
  echo "received request" . PHP_EOL;
  var_dump($request);

  // Check if the request has a type
  if (!isset($request["type"])) {
      return array("status" => "error", "message" => "Request type is missing.");
  }

  // Process the request based on its type
  switch ($request["type"]) {

    case "login":
    // Check if username and password are provided
      if (!isset($request["username"]) || !isset($request["password"])) {
          return array("status" => "error", "message" => "Login request is missing username or password.");
      }
      return doLogin($request["username"], $request["password"]);

    case "validate_session":
    // Check if sessionId is provided
      if (!isset($request["sessionId"])) {
          return array("status" => "error", "message" => "Session validation request is missing sessionId.");
      }
      return doValidate($request["sessionId"]);

    default:
    // If the request type is not recognized, return an error message
      return array("status" => "error", "message" => "Unsupported request type: " . $request["type"]);
  }
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "testRabbitMQServer BEGIN" . PHP_EOL;
$server->process_requests("requestProcessor");
echo "testRabbitMQServer END" . PHP_EOL;
exit();
?>