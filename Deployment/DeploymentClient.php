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
        case "mushran":
            switch($cluster){
                case "qa":
                    echo "1";
                    $client = new rabbitMQClient("guiltyDeployment.ini","frontend-QA");
                    return $client->send_request($request);
                    break;
                case "prod":
                    echo "2";
                    $client = new rabbitMQClient("guiltyDeployment.ini","frontend-Prod");
                    return $client->send_request($request);
                    break;
            }
            break;
        case "joe":
            switch($cluster){
                case "qa":
                    echo "3";
                    $client = new rabbitMQClient("guiltyDeployment.ini","database-QA");
                    return $client->send_request($request);
                    break;
                case "prod":
                    echo "4";
                    $client = new rabbitMQClient("guiltyDeployment.ini","database-Prod");
                    return $client->send_request($request);
                    break;
            }
            break;
        case "test":
            switch($cluster){
                case "qa":
                    echo "5";
                    $client = new rabbitMQClient("guiltyDeployment.ini","DMZ-QA");
                    return $client->send_request($request);
                    break;
                case "prod":
                    echo "6";
                    $client = new rabbitMQClient("guiltyDeployment.ini","DMZ-Prod");
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

