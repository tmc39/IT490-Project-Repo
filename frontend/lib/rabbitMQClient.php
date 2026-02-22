#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("rabbitMQ.ini","gsServer");
if (isset($argv[1]))
{
	$msg = $argv[1];
}
else
{
	$msg = "test message";
}

$request = array();
// FIX 1: changed 'Login' to 'login' to match the expected case in the server's request handling logic.
$request['type'] = "login";
$request['username'] = "gsUser";
$request['password'] = "psswrd";
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);

/*
FIX 2: Removed the extra 'j' character, from 'PHP_EOLj' to 'PHP_EOL'
	a) PHP_EOL: is a predefined constant in PHP that represents the end of a line, and it should for cross-platform compatibility. 
	b) It will be replaced with the appropriate line ending character(s) based on the operating system 
		NOTE: "\n" for Unix/Linux, "\r\n" for Windows).
*/
echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

?>
