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
    // Connect to database
    $db = getDbConnection();
    if ($db === null) {
        return array("status" => "error", "message" => "Database is not reachable at the moment.");
    }

    // Ensure required fields are provided
    if (trim($username) === "" || trim($hashedPassword) === "" || trim($email) === "") {
        $db->close();
        return array("status" => "error", "message" => "Missing required registration fields.");
    }

    // Prevent duplicate username
    $stmt = $db->prepare("SELECT username FROM users WHERE username = ?");
    if ($stmt === false) {
        $db->close();
        return array("status" => "error", "message" => "Could not prepare username check.");
    }
    // Bind username parameter and execute the query
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If username exists, return error message, close statement and DB connection
    if ($result->num_rows > 0) {
        $stmt->close();
        $db->close();
        return array("status" => "error", "message" => "Username already exists.");
    }
    // Done with SELECT statement
    $stmt->close();

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (username, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $db->close();
        return array("status" => "error", "message" => "Could not prepare insert statement.");
    }

    // Bind parameters and execute query
    $stmt->bind_param("sssss", $username, $hashedPassword, $email, $firstname, $lastname);

    // If execute fails, return error message, close statement and DB connection
    if (!$stmt->execute()) {
        $stmt->close();
        $db->close();
        return array("status" => "error", "message" => "Could not create user.");
    }

    // Done with INSERT statement
    $stmt->close();
    $db->close();

    // User created successfully
    return array("status" => "success", "message" => "User created.");
}

/*
-------------------
FUNCTION: doLogin()
-------------------
This function checks if the provided username and password are correct. If true, creates asession key and stores in DB.
*/
function doLogin($username, $password)
{
  // Connect to the database
  $db = getDbConnection();

  // If DB is down, we return a error message
  if ($db === null) {
      return array("status" => "error", "message" => "Database is not reachable at the moment.");
  }

  // Look up user in database
  $stmt = $db->prepare("SELECT password FROM users WHERE username = ?");

  // If prepare fails, return error message, close DB connection
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Database query could not be prepared.");
  }
  // Bind username parameter and execute query
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  // If user doesn't exist
  if ($result->num_rows === 0) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Login failed, username or password incorrect.");
  }

  // Fetch stored password for the user
  $row = $result->fetch_assoc();
  $storedPassword = $row["password"];

  // Using password_verify() because DB stores hashed passwords
  if (!password_verify($password, $storedPassword)) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Login failed, username or password incorrect.");
  }

  // Done with SELECT statement
  $stmt->close();

  // Create a session key
  $sessionKey = bin2hex(random_bytes(16));

  // Store session key in the sessions table
  $stmt = $db->prepare("INSERT INTO sessions (session_key, username) VALUES (?, ?)");

  // If prepare fails, return error message, and close DB connection
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Could not prepare statement to save session key.");
  }

  // Bind session key and username parameters and execute query
  $stmt->bind_param("ss", $sessionKey, $username);

  // If execute fails, return error message, close statement and DB connection
  if (!$stmt->execute()) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Could not save session key.");
  }

  // Done with INSERT statement
  $stmt->close();
  $db->close();

  // Send session key back so frontend can store in $_SESSION arrayy variable
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

  // If DB is down, we return a error message
  if ($db === null) {
      return array("status" => "error", "message" => "Database is not reachable at the moment.");
  }

  // Check if sessionId provided
  if (!isset($sessionId) || trim($sessionId) === "") {
      $db->close();
      return array("status" => "error", "message" => "No session key was provided.");
  }

  // Check if username provided
  if (!isset($username) || trim($username) === "") {
      $db->close();
      return array("status" => "error", "message" => "No username was provided.");
  }

  // Look up session key in the sessions db
  $stmt = $db->prepare("SELECT session_key FROM sessions WHERE session_key = ? AND username = ?");

  // If prepare fails, return error message, closee DB connection
  if ($stmt === false) {
      $db->close();
      return array("status" => "error", "message" => "Could not prepare statement to check session key.");
  }

  // Bind session key and username parameters and execute query
  $stmt->bind_param("ss", $sessionId, $username);
  $stmt->execute();
  $result = $stmt->get_result();

  // If session key doesn't exist
  if ($result->num_rows === 0) {
      $stmt->close();
      $db->close();
      return array("status" => "error", "message" => "Session key not valid.");
  }

  // Done with SELECT statement
  $stmt->close();
  $db->close();

  // Session key is valid
  return array("status" => "success", "message" => "Session key is valid.");
}

/*
----------------------------
FUNCTION: postReview()
----------------------------
This function is used when a request to post a recipe review is received by RabbitMQ.
*/
function postReview ($request){
    //try to connect to database
    $db = getDbConnection();
    if ($db === null) {
        return array("status" => "error", "message" => "Database is not reachable at the moment.");
    }
    $username = $request["username"];
    $recipeID = $request["recipe"];
    $positive = 1;
    if($request["positive"] == false){
        $positive = 0;
    }
    $reviewtext = $request["reviewtext"];

    //returns error if any values aren't set
    if($username == null) {
        return array("status" => "error", "message" => "Missing required variable " . "user: " . $username);
    }
    else if($recipeID == null){
        return array("status" => "error", "message" => "Missing required variable " . "recipe ID: " . $recipeID);
    }
    else if($positive == null){
        return array("status" => "error", "message" => "Missing required variable " . "ispositive: " . $positive);
    }
    else if($reviewtext == null){
        return array("status" => "error", "message" => "Missing required variable " . "review text: " . $reviewtext);
    }

    //prepare a MySQL statement to send to the database
    $stmt = $db->prepare("INSERT INTO recipereviews (recipeID, username, isPositive, reviewDescription) VALUES (?, ?, ?, ?)");
    //cancel if the preparation fails
    if($stmt === false){
        $db->close();
        return array("status" => "error", "message" => "Failed to prepare statement for database.");
    }

    // Bind the request's variables to the statement
    if(!$stmt->bind_param("ssss", $recipeID,$username,$positive,$reviewtext)){
        //exits if bind_param fails (indicated by it returning false)
        return array("status" => "error", "message" => "Could not bind values to SQL query.");
    }

    // If execute fails, we return an error message and close the statement and database connection
    if (!$stmt->execute()) {
        $stmt->close();
        $db->close();
        return array("status" => "error", "message" => "Could not post review.");
    }

    //close statement and databse connection
    $stmt->close();
    $db->close();
    

    echo "review posted" . PHP_EOL;
    return array("status" => "success", "message" => "Review has been posted.");
}

function listReviews($request){
    //gets the requested recipe ID
    $recipeID = $request["recipe"];
    if($recipeID == null){
        return json_encode(array("status" => "error", "message" => "Null RecipeID."), JSON_FORCE_OBJECT);
    }

    $db = getDbConnection();
    if ($db === null) {
        return json_encode(array("status" => "error", "message" => "Database is not reachable at the moment."), JSON_FORCE_OBJECT);
    }

    //prepare SQL statement to receive reviews for a specific recipeID
    $stmt = $db->prepare("SELECT * FROM recipereviews WHERE recipeID = ?;");
    if(!$stmt->bind_param("s", $recipeID)){
        //exits if bind_param fails (indicated by it returning false)
        return json_encode(array("status" => "error", "message" => "Could not bind values to SQL query."), JSON_FORCE_OBJECT);
    }

    //executes SQL statement
    if (!$stmt->execute()) {
        $stmt->close();
        $db->close();
        return json_encode(array("status" => "error", "message" => "Could not receive reviews."), JSON_FORCE_OBJECT);
    }

    //gets results of statement
    $results = $stmt->get_result();

    //returns if the results are null
    if($results == null){
        $stmt->close();
        $db->close();
        return json_encode(array("status" => "error", "message" => "No results from database."), JSON_FORCE_OBJECT);
    }

    if($results->num_rows <= 0){
        $stmt->close();
        $db->close();
        return json_encode(array("status" => "success", "message" => "There are no reviews for this recipe."), JSON_FORCE_OBJECT);
    }

    $resultsarray["status"] = "success";
    $resultsarray["message"] = "This recipe has " . $results->num_rows . " reviews.";
    $resultsarray["review"][0] = $results->fetch_assoc();

    for($i = 1; $i < $results->num_rows;$i++){
        $resultsarray["review"][] = $results->fetch_assoc();
    }

    $stmt->close();
    $db->close();
    
    return json_encode($resultsarray, JSON_FORCE_OBJECT);
}

/*
-----------------------------
FUNCTION: doSaveProfile()
-----------------------------
This function saves or updates a users dietary profile.
*/
function doSaveProfile($request)
{
    // Connect to the database
    $db = getDbConnection();

    // If DB is down, return error message
    if ($db === null) {
        return array("status" => "error", "message" => "Database is not reachable right now.");
    }

    // Get values from the request
    $username = $request["username"] ?? null;
    $goal = $request["dietary_goal"] ?? null;
    $calories = $request["calorie_target"] ?? null;
    $kosher = $request["kosher"] ?? 0;
    $halal = $request["halal"] ?? 0;
    $vegetarian = $request["vegetarian"] ?? 0;
    $vegan = $request["vegan"] ?? 0;
    $allergies = $request["allergies"] ?? null;

    // Check for usernme
    if ($username == null) {
        $db->close();
        return array("status" => "error", "message" => "Missing username.");
    }

    // Insert new profile or update if username exists
    $sql = "INSERT INTO user_profiles (username, dietary_goal, calorie_target, kosher, halal, vegetarian, vegan, allergies)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)

            ON DUPLICATE KEY UPDATE

            dietary_goal = VALUES(dietary_goal),
            calorie_target = VALUES(calorie_target),
            kosher = VALUES(kosher),
            halal = VALUES(halal),
            vegetarian = VALUES(vegetarian),
            vegan = VALUES(vegan),
            allergies = VALUES(allergies)";

    $stmt = $db->prepare($sql);

    // Cancel if preparation fails
    if ($stmt === false) {
        $db->close();
        return array("status" => "error", "message" => "Could not prepare profile query.");
    }

    // Bind parameters and execute the query
    $stmt->bind_param("ssiiiiis", $username, $goal, $calories, $kosher, $halal, $vegetarian, $vegan, $allergies);

    // If execute fails, return error message, close statement and DB connection
    if (!$stmt->execute()) {
        $stmt->close();
        $db->close();
        return array("status" => "error", "message" => "Could not save profile.");
    }

    // Done with the statement
    $stmt->close();
    $db->close();

    return array("status" => "success", "message" => "Profile saved.");
}

/*
----------------------------
FUNCTION: doGetProfile()
----------------------------
This function loads a user's dietary profile.
*/
function doGetProfile($username)
{
    // Connect to the database
    $db = getDbConnection();

    // If DB is down, return a error message
    if ($db === null) {
        return array("status" => "error", "message" => "Database is not reachable at the moment.");
    }

    // Check if username provided
    if (!isset($username) || trim($username) === "") {
        $db->close();
        return array("status" => "error", "message" => "No username was provided.");
    }

    // Look up user's profile
    $stmt = $db->prepare("SELECT dietary_goal, calorie_target, kosher, halal, vegetarian, vegan, allergies FROM user_profiles WHERE username = ?");

    // Cancel if preparation fails
    if ($stmt === false) {
        $db->close();
        return array("status" => "error", "message" => "Could not prepare profile query.");
    }

    // Bind username and execute the query
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If no profile exists, return success with empty values
    if ($result->num_rows === 0) {
        $stmt->close();
        $db->close();
        return array("status" => "success", "message" => "No profile found.",
            "profile" => array(
                "dietary_goal" => "",
                "calorie_target" => "",
                "kosher" => 0,
                "halal" => 0,
                "vegetarian" => 0,
                "vegan" => 0,
                "allergies" => ""
            )
        );
    }

    $row = $result->fetch_assoc();

    // Done with the statement
    $stmt->close();
    $db->close();

    return array("status" => "success", "message" => "Profile loaded.", "profile" => $row);
}

/*
----------------------------
FUNCTION: requestProcessor()
----------------------------
This function is called whenever a new request is received.
*/
function requestProcessor($request)
{
  echo "received request" . PHP_EOL;
  var_dump($request);

  // Check if the request has a type
  if (!isset($request["type"])) {
      return array("status" => "error", "message" => "Request type is missing.");
  }

  // Process request based on it's type
  switch ($request["type"]) {

    case "register":
    // Check if all required fields provided
        if (!isset($request["firstname"], $request["lastname"], $request["email"], $request["username"], $request["password"])) {
            return array("status" => "error", "message" => "Register request is missing fields.");
        }
        return doRegister(
            // NOTE: password already hashed by client side
            $request["firstname"], $request["lastname"], $request["email"], $request["username"], $request["password"]
        );

    case "login":
    // Check if username and password provided
      if (!isset($request["username"]) || !isset($request["password"])) {
          return array("status" => "error", "message" => "Login request is missing username or password.");
      }
      return doLogin($request["username"], $request["password"]);

    case "validate_session":
    // Check if sessionId provided
      if (!isset($request["sessionId"]) || !isset($request["username"])) {
          return array("status" => "error", "message" => "Session validation request is missing sessionId or username.");
      }
      return doValidate($request["sessionId"], $request["username"]);

    case "post_review":
        echo "attempting to post review" . PHP_EOL;
        return postReview($request);
    case "load_reviews":
        echo "attempting to load reviews" . PHP_EOL;
        return listReviews($request);

    case "save_profile":
    // Check if username provided
        if (!isset($request["username"])) {
            return array("status" => "error", "message" => "Profile request is missing username.");
        }
        return doSaveProfile($request);

    case "get_profile":
    // Check if username provided
        if (!isset($request["username"])) {
            return array("status" => "error", "message" => "Profile request is missing username.");
        }
        return doGetProfile($request["username"]);

    default:
    // If request type not recognized, return error message
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