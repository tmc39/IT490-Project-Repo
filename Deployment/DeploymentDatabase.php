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

function addVersion($machine, $version){
        $db = getdbConnection();
        $query = "INSERT INTO bundles (Bundlename, Status, Machine) VALUES ('$version', 'new', '$machine');";
        $db->query($query);
        $db->close();
}

function lastGood($machine){
    $db = getdbConnection();
    $query = "SELECT Bundlename FROM bundles WHERE Status = 'good' AND Machine = '$machine' ORDER BY Versionnumber DESC LIMIT 1;";
    $result = $db->query($query);
    $db->close();
    return $result;
}


function updateVersion($status, $machine){
    $db = getdbConnection();
    $query = " ";
    if($status == "passed"){
        $query = "UPDATE bundles SET Status='good' WHERE Status = 'new' AND Machine = '$machine';";
    }
    else if($status == "failed"){
        $query = "UPDATE bundles SET Status='failed' WHERE Status = 'new' AND Machine = '$machine';";
        return lastGood($machine);
    }
    $db->query($query);
    $db->close();
}

?>
