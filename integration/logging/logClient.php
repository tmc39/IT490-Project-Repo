<?php
/*
-------------
logClient.php
-------------
1) Sends log messages to RabbitMQ instead of echoing errors.
2) Used by frontend, backend, and database when errors occur.
*/

require_once(__DIR__ . '/../lib/rabbitMQLib.inc');

/*
----------------
sendLogMessage()
----------------
$message = error message
$level   = ERROR, WARNING, INFO
$source  = where it came from
*/
function sendLogMessage($message, $level = "ERROR", $source = "UNKNOWN")
{
    // build log data
    $logData = [
        "timestamp" => date("Y-m-d H:i:s"),
        "level" => $level,
        "source" => $source,
        "message" => $message
    ];

    try {
        // NOTE: to test locally use "testServer" 
        // NOTE: to test over ZeroTier use "guiltyDatabase"
        $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', "testServer");

        // send log message (current library format)
        $client->publish(json_encode($logData));

    } catch (Exception $e) {

        // fallback if RabbitMQ fails
        file_put_contents(
            "/tmp/local_fallback.log",
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
}
?>