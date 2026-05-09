<?php
/*
-----------------------
rabbitMQ_web_client.php
-----------------------
Small helper used by web pages to send requests to RabbitMQ.
*/

function sendToRabbitMQ(array $request)
{
    // Path to RabbitMQ library
    $integrationLib = realpath(__DIR__ . '/../../integration/lib');

    if ($integrationLib === false) {
        throw new Exception("Could not find integration/lib folder.");
    }

    // Load RabbitMQ library
    require_once $integrationLib . '/rabbitMQLib.inc';

    // NOTE: to test locally use "testServer" 
    // NOTE: to test over ZeroTier use "guiltyDatabase"
    $client = new rabbitMQClient("testRabbitMQ.ini","testServer");

    // Send request and wait for reply
    return $client->send_request($request);
}