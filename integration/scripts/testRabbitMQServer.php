#!/usr/bin/php
<?php
require_once(__DIR__ . '/../lib/path.inc');
require_once(__DIR__ . '/../lib/get_host_info.inc');
require_once(__DIR__ . '/../lib/rabbitMQLib.inc');
require_once(__DIR__ . '/../../database/mysqlconnect.php');

/*
----------------------
FUNCTION: doRegister()
----------------------
This function registers a new user in the database.
*/
function doRegister($firstname, $lastname, $email, $username, $hashedPassword)
{
    // Connect to the database
    $db = getDbConnection();
    if ($db === null) {
        return array("status" => "error", "message" => "Database is not reachable at the moment.");
    }

    // Basic checks to ensure required fields are provided
    if (trim($username) === "" || trim($hashedPassword) === "" || trim($email) === "") {
        $db->close();
        return array("status" => "error", "message" => "Missing required registration fields.");
    }

    // Prevent duplicate usernames
    $stmt = $db->prepare("SELECT username FROM users WHERE username = ?");
    if ($stmt === false) {
        $db->close();
        return array("status" => "error", "message" => "Could not prepare username check.");
    }
    // Bind the username parameter and execute the query
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If username already exists, return an error message and close the statement and database connection
    if ($result->num_rows > 0) {
        $stmt->close();
        $db->close();
        return array("status" => "error", "message" => "Username already exists.");
    }
    // Done with the SELECT statement
    $stmt->close();

    // Insert user (store hashed password exactly as received, do NOT hash again!)
    $stmt = $db->prepare("INSERT INTO users (username, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $db->close();
        return array("status" => "error", "message" => "Could not prepare insert statement.");
    }

    // Bind the parameters and execute the query
    $stmt->bind_param("sssss", $username, $hashedPassword, $email, $firstname, $lastname);

    // If execute fails, return an error message and close the statement and database connection
    if (!$stmt->execute()) {
        $stmt->close();
        $db->close();
        return array("status" => "error", "message" => "Could not create user.");
    }

    // Done with the INSERT statement
    $stmt->close();
    $db->close();

    // The user was created successfully
    return array("status" => "success", "message" => "User created.");
}

/*
-------------------
FUNCTION: doLogin()
-------------------
This function checks if the provided username and password are correct. If they are, it creates a session key and stores it in the database.
*/
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

  // Using password_verify() because DB now stores hashed passwords
  if (!password_verify($password, $storedPassword)) {
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

/*
----------------------
FUNCTION: doValidate()
----------------------
This function checks if a given session key is valid for a given username.
*/
function doValidate($sessionId, $username)
{
  // Connect to the database
  $db = getDbConnection();

  // If DB is down, we return a clear error message
  if ($db === null) {
      return array("status" => "error", "message" => "Database is not reachable at the moment.");
  }

  // Check if sessionId is provided
  if (!isset($sessionId) || trim($sessionId) === "") {
      $db->close();
      return array("status" => "error", "message" => "No session key was provided.");
  }

  // Check if username is provided
  if (!isset($username) || trim($username) === "") {
      $db->close();
      return array("status" => "error", "message" => "No username was provided.");
  }

  // Look up the session key in the database
  $stmt = $db->prepare("SELECT session_key FROM sessions WHERE session_key = ? AND username = ?");

  // If prepare fails, $stmt is false, so we can only close $db
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Could not prepare statement to check session key.");
  }

  // Bind the session key and username parameters and execute the query
  $stmt->bind_param("ss", $sessionId, $username);
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

  // The session key is valid
  return array("status" => "success", "message" => "Session key is valid.");
}

/*
----------------------------
FUNCTION: requestProcessor()
----------------------------
This function is called by the RabbitMQ server whenever a new request is received.
*/
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

    case "register":
    // Check if all required fields are provided
        if (!isset($request["firstname"], $request["lastname"], $request["email"], $request["username"], $request["password"])) {
            return array("status" => "error", "message" => "Register request is missing fields.");
        }
        return doRegister(
            // NOTE: the password is already hashed by the client, so do NOT hash it again!
            $request["firstname"], $request["lastname"], $request["email"], $request["username"], $request["password"]
        );

    case "login":
    // Check if username and password are provided
      if (!isset($request["username"]) || !isset($request["password"])) {
          return array("status" => "error", "message" => "Login request is missing username or password.");
      }
      return doLogin($request["username"], $request["password"]);

    case "validate_session":
    // Check if sessionId is provided
      if (!isset($request["sessionId"]) || !isset($request["username"])) {
          return array("status" => "error", "message" => "Session validation request is missing sessionId or username.");
      }
      return doValidate($request["sessionId"], $request["username"]);

    default:
    // If the request type is not recognized, return an error message
      return array("status" => "error", "message" => "Unsupported request type: " . $request["type"]);
  }
}

// NOTE: to test locally use "testServer" 
// NOTE: to test over ZeroTier use "guiltyDatabase"
$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "testRabbitMQServer BEGIN" . PHP_EOL;
$server->process_requests("requestProcessor");
echo "testRabbitMQServer END" . PHP_EOL;
exit();
?>