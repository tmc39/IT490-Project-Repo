#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

<<<<<<<< HEAD:database/testRMQClient.php
$client = new rabbitMQClient("testRMQ.ini","testServer");
========
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
>>>>>>>> refs/remotes/origin/database:integration/scripts/testRabbitMQClient.php
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
<<<<<<<< HEAD:database/testRMQClient.php
$request['type'] = "login";
$request['username'] = "test";
$request['password'] = "12345";
========
$request['type'] = "Login";
$request['username'] = "steve";
$request['password'] = "password";
>>>>>>>> refs/remotes/origin/database:integration/scripts/testRabbitMQClient.php
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

