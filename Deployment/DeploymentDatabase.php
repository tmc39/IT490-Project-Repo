#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');

function getdbConnection(){
    $mydb = new mysqli('127.0.0.1', 'deploy', 'password', 'deploy');

    if ($mydb->connect_error){
        return null;
    }

    return $mydb;
}

function addVersion($machine, $number, $version){
        $db = getdbConnection();
        $query = "INSERT INTO bundles (Bundlename, Versionnumber, Status, Machine) VALUES ('$version', $number, 'new', '$machine');";
        $db->query($query);
        $db->close();
}

function lastGood($machine){
    $db = getdbConnection();
    $query = "SELECT MAX(Versionnumber) FROM bundles (Bundlename, Versionnumber, Status, Machine) WHERE Status = 'good' AND Machine = '$machine';";
    $result = $db->query($query);
    $db->close();
    return $result;
}


function updateVersion($version, $status, $machine){
    $db = getdbConnection();
    $query = " ";
    if($status == "passed"){
        $query = "UPDATE bundles SET Status='good' WHERE Bundlename=$version;";
    }
    else if($status == "failed"){
        $query = "UPDATE bundles SET Status='failed' WHERE Bundlename=$version;";
        return lastGood($machine);
    }
    $db->query($query);
    $db->close();
}

?>
