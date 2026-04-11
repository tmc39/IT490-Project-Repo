#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once __DIR__ . '/DeploymentClient.php';

$versionName = "test-1-0";
//Runs the bash packaging script, passing the version name as a parameter
$output = shell_exec("./packaging.sh $versionName");

$request = [
    "type" => "new_version",
    "machine" => "message-broker",
    "ip" => "192.168.220.131",
    "path" => "/home/message-broker/Deployment-Server/Versions/$versionName.zip",
    "version" => $versionName,
];

echo sendVersion($request);
?>
