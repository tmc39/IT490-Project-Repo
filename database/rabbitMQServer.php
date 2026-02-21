#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
	//lookup username in database
	//check password
	return true;
	//return false if not valid
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

$server = new rabbitMQServer("rabbitMQ.ini","gsServer");

$server->process_requests('requestProcessor');
exit();
?>


