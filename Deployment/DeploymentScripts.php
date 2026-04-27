#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once __DIR__ . '/UpdateClient.php';

function sendBundle(){
    $cluster = "temp";

    do{
        $cluster = readline("Destination Cluster? (qa/prod): ");
        chop($cluster);
    }while(!($cluster == "qa" || $cluster == "prod" || $cluster == "test"));


    $versionName = readline("Enter bundle name: ");
    chop($versionName);

    //Runs the bash packaging script, passing the version name as a parameter
    $success = shell_exec("./packaging.sh $versionName");

    $machine = get_current_user();

    $path = "/home/message-broker/Deployment-Server/Versions/$versionName.zip";

    $ip = gethostbyname($machine);

    $request = [
        "type" => "new_version",
        "machine" => "$machine",
        "ip" => $ip,
        "path" => $path,
        "version" => $versionName,
        "cluster" => $cluster,
    ];

    echo sendRequest($request);
}

function updateStatus(){
    do{
        $status = readline("Bundle status(passed/failed): ");
        chop($status);
    }while(!($status == "passed" || $status == "failed"));

    $ip = gethostbyname($machine);

    $request = [
        "type" => "versionValidate",
        "machine" => "message-broker",
        "status" => $input
        "ip" => $ip,
        "version" => $versionName,
        "cluster" => "qa",
    ];

    echo sendRequest($request);
}

$input = 'temp';
do{
    $input = readline("Function to perform(bundle/status): ");
    chop($input);
}while(!($cluster == "bundle" || $cluster == "status"));

switch($input):
    case('bundle'):
        sendBundle();
        break;
    case('status'):
        updateStatus();
        break;
?>
