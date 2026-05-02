<?php
/*
-----------------------
rabbitMQ_web_client.php
-----------------------
Small helper used by web pages to send requests to RabbitMQ.
*/

require_once(__DIR__ . '/../../integration/logging/logClient.php');

function sendToRabbitMQ(array $request)
{
    // Path to RabbitMQ library
    $integrationLib = realpath(__DIR__ . '/../../integration/lib');

    if ($integrationLib === false) {
        sendLogMessage(
            "Could not find integration/lib folder.",
            "ERROR",
            "frontend"
        );

        throw new Exception("Could not find integration/lib folder.");
    }

    // Load RabbitMQ library
    require_once $integrationLib . '/rabbitMQLib.inc';

    // NOTE: to test locally use "testServer"
    // NOTE: to test over ZeroTier use "guiltyDatabase"
    $rabbitServer = "testServer";

    // Check that RabbitMQ config section exists before using it
    $configPath = __DIR__ . '/../../integration/config/testRabbitMQ.ini';
    $config = parse_ini_file($configPath, true);

    if (!isset($config[$rabbitServer])) {
        sendLogMessage(
            "RabbitMQ config section not found: " . $rabbitServer,
            "ERROR",
            "frontend"
        );

        throw new Exception("RabbitMQ config section not found: " . $rabbitServer);
    }

    try {
        $client = new rabbitMQClient("testRabbitMQ.ini", $rabbitServer);

        // Send request and wait for reply
        $response = $client->send_request($request);

        if (!$response) {
            sendLogMessage(
                "RabbitMQ returned an empty response.",
                "ERROR",
                "frontend"
            );
        }

        return $response;

    } catch (Exception $e) {
        sendLogMessage(
            "RabbitMQ request failed: " . $e->getMessage(),
            "ERROR",
            "frontend"
        );

        throw $e;
    }
}