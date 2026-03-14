#!/usr/bin/php
<?php
require_once(__DIR__ . '/../lib/path.inc');
require_once(__DIR__ . '/../lib/get_host_info.inc');
require_once(__DIR__ . '/../lib/rabbitMQLib.inc');

// NOTE: to test locally use "testServer" 
// NOTE: to test over ZeroTier use "guiltyDatabase"
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

/*
// get_profile test
$request = array();
$request['type'] = "get_profile";
$request['username'] = "NJ2026";
*/


// save_profile test
$request = array();
$request['type'] = "save_profile";
$request['username'] = "NJ2026";
$request['dietary_goal'] = "weight_loss";
$request['calorie_target'] = 2300;
$request['kosher'] = 0;
$request['halal'] = 0;
$request['vegetarian'] = 0;
$request['vegan'] = 1;
$request['allergies'] = "strawberry";


/*
$request = array();
$request['type'] = "login";
$request['username'] = "steve";
$request['password'] = "password";
$request['message'] = $msg;
*/

$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
?>