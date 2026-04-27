#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('DeploymentDatabase.php');
require_once('DeploymentClient.php');
/*
 *
 * THIS FILE IS ONLY TO BE USED ON THE DEPLOYMENT SERVER
 *
 */
#Pulls a bundle from a machine and stores it on the machine using the scp command (Currently works)
function pullNewVersion($machine, $ip, $path, $version, $cluster)
{
  $success = shell_exec("scp $machine@$ip:$path /home/message-broker/Deployment-Server/Versions");

  //if(!$success){
 //   return "Pull unsuccessful";
  //}
  //else{
    //Run function for adding version to database
    addVersion($machine, 2, $version);
    $ip = gethostbyname($machine);
    //Creates a request to send the new version to the machine it is to be installed on
    $request = [
      "type" => "update",
      "ip" => $ip,
      "path" => "/home/message-broker/Deployment-Server/Versions/$version.zip",
      "version" => $version,
    ];

    echo sendNewVersion($request, $machine, $cluster);
    return "Pull successful";
 // }
}

//Function to update the status of a package following testing
function updateStatus($version, $status, $machine)
{
  //Run SQL commandds to update the database with the validation data
  if($status == "failed")
  {
    //needs to pull the name of the last good version from the database
    updateVersion($version, "failed", $machine);

    $ip = gethostbyname($machine);

    $request = [
      "type" => "update",
      "ip" => $ip,
      "path" => "/home/message-broker/Deployment-Server/Versions/$version.zip",
      "version" => $version,
    ];
  }
  else if($status == "passed")
  {
    //Run SQL command to update
    updateVersion($version, "good", $machine);
  }
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
    //Switch statement covers responses to different types of requests
    case "new_version":
      return pullNewVersion($request['machine'], $request['ip'], $request['path'], $request['version'], $request['cluster']);
    case "versionValidate":
      return updateStatus($request['version'], $request['status'], $request['machine']);

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

