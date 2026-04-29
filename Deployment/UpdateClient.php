#!/usr/bin/php
<?php
// This is the client meant to send out messages from machines to the deployment server
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function sendRequest(array $request)
{
    $client = new rabbitMQClient("guiltyRabbitMQ.ini","guiltyDeployment");

    // Send request and wait for reply
    return $client->send_request($request);
}

?>

