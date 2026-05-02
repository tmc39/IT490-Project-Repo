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
    $logDir = "/var/log/guiltyspark";
    $logFile = $logDir . "/distributed.log";

    // create log directory if it does not exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    // build readable log entry
    $logEntry =
        "{\n" .
        "    timestamp: " . ($request["timestamp"] ?? "N/A") . ",\n" .
        "    level: " . ($request["level"] ?? "N/A") . ",\n" .
        "    source: " . ($request["source"] ?? "N/A") . ",\n" .
        "    message: " . ($request["message"] ?? "N/A") . "\n" .
        "}\n\n";

    // append log to file with spacing between entries
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    return [
        "status" => "logged",
        "message" => "Log saved"
    ];
}

// NOTE: logServer is the RabbitMQ section used only for logging
$server = new rabbitMQServer(__DIR__ . '/../config/testRabbitMQ.ini', "logServer");

// receive log messages from RabbitMQ
$server->process_requests("logCallback");
?>