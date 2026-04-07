   #!/usr/bin/php
<?php
//This file does not define the rabbitMQ client class, it simply creates one. 
//rabbitMQLib.inc is obviously needed for classes, not sure about the other two. 
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function sendNewVersion(array $request)
{

    $client = new rabbitMQClient("testRabbitMQ.ini","testServer");

    // Send request and wait for reply
    return $client->send_request($request);
}

