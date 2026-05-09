#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('DeploymentDatabase.php');

#Pulls a bundle from a machine and stores it on the machine using the scp command (Currently works)
function pullNewVersion($ip, $path, $version)
{
  $success = shell_exec("scp message-broker@$ip:$path /home/message-broker/Deployment-Server/Versions");
  #These return statements don't work for some reason
  if(is_null($success)){
    return "Pull unsuccessful";
  }
  else{
    //Run function for installing new package    return "Pull successful";
  }
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
    case "update":
      return pullNewVersion($request['ip'], $request['path'], $request['version']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

//instantiates new server object
$server = new rabbitMQServer("guiltyRabbitMQ.ini","guiltyDeployment");

echo "testRabbitMQServer BEGIN".PHP_EOL;
//process_requests starts the server and has it listen for messages
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

