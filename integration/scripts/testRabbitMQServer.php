#!/usr/bin/php
<?php
require_once(__DIR__ . '/../lib/path.inc');
require_once(__DIR__ . '/../lib/get_host_info.inc');
require_once(__DIR__ . '/../lib/rabbitMQLib.inc');

function doLogin($username,$password)
{
    // lookup username in databas
    // check password
    return true;
    //return false if not valid
}

function doValidate($sessionId)
{
    // Simple placeholder for now
    // Later, this will query the database and confirm the session key exists.

    if (!isset($sessionId) || trim($sessionId) === '') {
        return false; // Invalid session ID
    }

    // For testing only. If a sessionId was provided, we pretend it's valid.
    // Replace this with a real database check ond the DB listner is wired up.
    return true;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

