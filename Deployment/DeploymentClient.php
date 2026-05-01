   #!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//This client is meant to send out updates from the deployment server to the other machines

function sendNewVersion(array $request, $machine, $cluster)
{
    //Switch statement used to decide where the new version is sent to
    switch ($machine){
        case "Mushran":
            switch($cluster){
                case "qa":
                    $client = new rabbitMQClient("guiltyDeployment.ini","frontend-QA");
                    return $client->send_request($request);
                    break;
                case "prod":
                    $client = new rabbitMQClient("guiltyDeployment.ini","frontend-Prod");
                    return $client->send_request($request);
                    break;
            }
            break;
        case "joe":
            switch($cluster){
                case "qa":
                    $client = new rabbitMQClient("guiltyDeployment.ini","database-QA");
                    return $client->send_request($request);
                    break;
                case "prod":
                    $client = new rabbitMQClient("guiltyDeployment.ini","database-Prod");
                    return $client->send_request($request);
                    break;
            }
            break;
        case "test":
            switch($cluster){
                case "qa":
                    $client = new rabbitMQClient("guiltyDeployment.ini","dmz-QA");
                    return $client->send_request($request);
                    break;
                case "prod":
                    $client = new rabbitMQClient("guiltyDeployment.ini","dmz-Prod");
                    return $client->send_request($request);
                    break;
            }
            break;
        case "message-broker":
            $client = new rabbitMQClient("guiltyDeployment.ini","test");
            return $client->send_request($request);
            break;
    }


    // Send request and wait for reply
    return $client->send_request($request);
}

