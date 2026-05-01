<?php
/*
---------------
logListener.php
---------------
NOTE: this is the Distributed Log Listener (LL)

1) Runs on each VM (FE, BE, DMZ).
2) Listens for log messages from RabbitMQ and writes them to a local file.
*/

require_once(__DIR__ . '/../lib/rabbitMQLib.inc');

/*
-----------------
Callback Function
-----------------
Runs whenever a log message is received.
*/
function logCallback($request)
{
    $logFile = "/var/log/guiltyspark/distributed.log";

    // append log to file
    file_put_contents(
        $logFile,
        json_encode($request) . PHP_EOL,
        FILE_APPEND
    );

    return [
        "status" => "logged",
        "message" => "Log saved"
    ];
}

// NOTE: to test locally use "testServer"
// NOTE: to test over ZeroTier use "guiltyDatabase"
$server = new rabbitMQServer(__DIR__ . '/../config/testRabbitMQ.ini', "testServer");

// receive log messages using the current RabbitMQ library
$server->process_requests("logCallback");
?>