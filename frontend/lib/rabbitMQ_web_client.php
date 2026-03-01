<?php
/*
-----------------------
rabbitMQ_web_client.php
-----------------------
This file is used by our web pages (like login.php and register.php)
to talk to RabbitMQ.

Why did we NOT use rabbitMQClient.php directly?
    1) Because that file was written to be run from the terminal. 
    2) It expects command line input and prints results to the screen.
        NOTE: in the RabbitMQ video tutorial, we used ./testRabbitMQClient.php "Hey this is a variable message here" to  send a message to the testRabbitMQServer.php terminal program. This is a great way to test RabbitMQ, but it’s not how we want to build our web app.

In our web app:
    1) There is no $argv (arguments from the command line)
    2) Printing directly can break redirects
    3) We want something simple and reusable
        NOTE:So instead of changing the professor’s file, we created this small helper file just for the WEB SERVER side. This keeps things clean and easier to understand later.
*/

// Load the main RabbitMQ library.
// NOTE: '__DIR__' a magic constant, makes sure PHP always knows where this file is. Otherwise, PHP only checks the current working directory.
require_once __DIR__ . '/rabbitMQLib.inc';

// This function sends a request to RabbitMQ and waits for a response from the backend.
// We keep it simple, pass in an array and get back an array.
function sendToRabbitMQ($request)
{
    // select the "gsServer" section name inside rabbitMQ.ini
    $client = new rabbitMQClient(__DIR__ . "/rabbitMQ.ini", "gsServer");

    // send_request waits for the backend to reply
    $response = $client->send_request($request);

    return $response;
}
?>