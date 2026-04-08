#!/usr/bin/php
<?php
require_once(__DIR__ . '/../lib/path.inc');
require_once(__DIR__ . '/../lib/get_host_info.inc');
require_once(__DIR__ . '/../lib/rabbitMQLib.inc');
//require_once(__DIR__ . '/../../database/mysqlconnect.php');

//These scripts contain the functions used by each request type
require_once(__DIR__ . '/api/foodInfo.php');
require_once(__DIR__ . '/api/foodSearch.php');
require_once(__DIR__ . '/api/recipeInfo.php');
require_once(__DIR__ . '/api/recipeSearch.php');

/*
----------------------------
FUNCTION: requestProcessor()
----------------------------
This function is called by the RabbitMQ server whenever a new request is received.
*/
function requestProcessor($request)
{
  echo "received request" . PHP_EOL;
  var_dump($request);

  // Check if the request has a type
  if (!isset($request["type"])) {
      return array("status" => "error", "message" => "Request type is missing.");
  }

  // Process the request based on its type
  switch ($request["type"]) {

    case "recipe_search":
        //check for paremeters
        if(isset($request["search"])){
            return array("status" => "error", "message" => 'Missing parameter search.');
        }
        if(isset($request["maxresults"])){
            return array("status" => "error", "message" => 'Missing parameter maxresults.');
        }
        if(isset($request["page"])){
            return array("status" => "error", "message" => 'Missing parameter page.');
        }

        $result = recipeSearch($request["search"], $request["maxresults"], $request["page"]);
        
        //return an error if no status is received
        if(!isset($result["status"])){
            return array("status" => "error", "message" => "Failed to get response from methods.");
        }
        
        return $result;

    case "recipe_info":
        //check for paremeters
        if(isset($request["search"])){
            return array("status" => "error", "message" => 'Missing parameter search.');
        }

        $result = recipeInfo($request["search"]);

        //return an error if no status is received
        if(!isset($result["status"])){
            return array("status" => "error", "message" => "Failed to get response from methods.");
        }
        
        return $result;

    case "food_search":
        //check for paremeters
        if(isset($request["search"])){
            return array("status" => "error", "message" => 'Missing parameter search.');
        }
        if(isset($request["maxresults"])){
            return array("status" => "error", "message" => 'Missing parameter maxresults.');
        }
        if(isset($request["page"])){
            return array("status" => "error", "message" => 'Missing parameter page.');
        }

        $result = foodSearch($request["search"], $request["maxresults"], $request["page"]);

        //return an error if no status is received
        if(!isset($result["status"])){
            return array("status" => "error", "message" => "Failed to get response from methods.");
        }
        
        return $result;

    case "food_info":
        //check for paremeters
        if(isset($request["search"])){
            return array("status" => "error", "message" => 'Missing parameter search.');
        }

        $result = foodInfo($request["search"]);

        //return an error if no status is received
        if(!isset($result["status"])){
            return array("status" => "error", "message" => "Failed to get response from methods.");
        }
        
        return $result;

    default:
    // If the request type is not recognized, return an error message
      return array("status" => "error", "message" => "Unsupported DMZ request type: " . $request["type"]);
  }
}

// NOTE: to test locally use "testDMZ" 
// NOTE: to test over ZeroTier use "guiltyDMZ"
$server = new rabbitMQServer("testRabbitMQ.ini", "testDMZ");

echo "RabbitMQ DMZ BEGIN" . PHP_EOL;
$server->process_requests("requestProcessor");
echo "RabbitMQ DMZ END" . PHP_EOL;
exit();
?>