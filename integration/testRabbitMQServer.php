#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
//require_once(''); include database file here (Im just making a bunch of code that might be useful once we figure out the broker thing)
//Possibly we could include the client file here as a way to have the server send messages, idk

function doLogin($username,$password)
{
    // lookup username in database
    // check password
    return true;//database login check function instead of true here
    //return false if not valid
}

function registerUser($username,$password,$email,$firstname,$lastname)
{
	//Call database function to insert all info into table
	
	//Prob should have a similar function that drops a user from the database
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  //This function displays the contents of the request in the terminal in a structured format
  var_dump($request);
  
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    //Switch statement covers responses to different types of requests
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

//instantiates new server object
$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
//process_requests starts the server and has it listen for messages
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

