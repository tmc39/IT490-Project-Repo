#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once __DIR__ . '/UpdateClient.php';

/*
 for testing purposes, just put test when prompted for a cluster and make
 the version's name whatever you want
 */
function sendBundle(){
    $cluster = "temp";

    do{
        $cluster = readline("Destination Cluster? (qa/prod): ");
        chop($cluster);
    }while(!($cluster == "qa" || $cluster == "prod" || $cluster == "test"));


    $versionName = readline("Enter bundle name: ");
    chop($versionName);

    $current = getcwd();

    chdir("..");
    chdir("..");
    $cwd = getcwd() . "/Versions";
    $path = "$cwd/$versionName.zip";

    chdir($current);

    //Runs the bash packaging script, passing the version name as a parameter
    $success = shell_exec("./packaging.sh $versionName $cwd");
    return null;

    $machine = get_current_user();

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

    $machine = get_current_user();

    $ip = gethostbyname($machine);

    $request = [
        "type" => "versionValidate",
        "machine" => $machine,
        "status" => $status,
        "ip" => $ip,
        #"version" => $versionName,
    ];

    echo sendRequest($request);
}

$input = 'temp';
do{
    $input = readline("Function to perform(bundle/status): ");
    chop($input);
}while(!($input == "bundle" || $input == "status"));

switch($input){
    case('bundle'):
        sendBundle();
        break;
    case('status'):
        updateStatus();
        break;
}
?>
