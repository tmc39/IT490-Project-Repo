<?php
/*
-----------------------
rabbitMQ_web_client.php
-----------------------

This is a small helper file for the web pages (login.php, home.php, register.php).

Important note:
1) We only pass the INI filename (example: "testRabbitMQ.ini").
2) Our get_host_info.inc already knows to load config files from integration/config/.
*/

function sendToRabbitMQ(array $request)
{
    // Point PHP to the integration/lib folder so rabbitMQLib.inc can find its helpers.
    $integrationLib = realpath(__DIR__ . '/../../integration/lib');
    if ($integrationLib === false) {
        throw new Exception("Could not find integration/lib folder on this machine.");
    }

    set_include_path(get_include_path() . PATH_SEPARATOR . $integrationLib);

    // Load the RabbitMQ library (it will pull in get_host_info.inc internally)
    require_once 'rabbitMQLib.inc';

    // We are using testServer for development right now
    $iniFile = "testRabbitMQ.ini";
    $serverSection = "testServer";

    // Create RabbitMQ client and send request
    $client = new rabbitMQClient($iniFile, $serverSection);

    // send_request waits for a reply
    $response = $client->send_request($request);

    return $response;
}