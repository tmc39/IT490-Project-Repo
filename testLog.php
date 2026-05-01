<?php
require_once(__DIR__ . "/integration/logging/logClient.php");

sendLogMessage("Test distributed logging message", "ERROR", "frontend");
?>
