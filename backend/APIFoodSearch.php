<?php

/*
/   This script's job is to use FatSecret's food search and return the relevant results data in JSON format.
/   parameters are put into this script via URL parameters
*/

require_once(__DIR__ . '/../integration/logging/logClient.php');

//Include the file that stores needed keys. This should hopefully prevent my super secret keys from being leaked on Github.
//uses key varaibles called $O1_Consumer_Key (ID key) and $O1_Consumer_Secret (secret key), used for FatSecret's oauth 1.0 URL-based authentication
require './BigFatKeys.php';


// access the URL parameters provided. If they are null, set placeholder values

$searchQuery = $_GET['search'] ?? null;
if($searchQuery == null){
    sendLogMessage(
        "Food search request missing search query. Defaulting to bagel.",
        "WARNING",
        "backend-api"
    );

    $searchQuery = "bagel";
}

$maxresults = $_GET['results'] ?? null;
if($maxresults == null){
    sendLogMessage(
        "Food search request missing results value. Defaulting to 10.",
        "WARNING",
        "backend-api"
    );

    $maxresults = 10;
}

$page = $_GET['page'] ?? null;
if($page == null){
    sendLogMessage(
        "Food search request missing page value. Defaulting to 0.",
        "WARNING",
        "backend-api"
    );

    $page = 0;
}

$ch = curl_init();


//https://platform.fatsecret.com/docs/guides/authentication/oauth1


//adds the given search query into the curl session's url
//mainUrl is used for signature encoding
$mainUrl = "https://platform.fatsecret.com/rest/foods/search/v1";
$url = 'https://platform.fatsecret.com/rest/foods/search/v1?';

//stupid annoying FatSecret oauth 1.0 required parameters
//PARAMETERS MUST BE IN ALPHABETICAL ORDER!!!!!!!!!! THIS  IS NEEDED FOR THE AUTHENTICATION SIGNATURE
//url will be the actual URL of the request. params will be used in the hashed signature

$params = 'format=json';
$url .= 'format=json';

$params .= "&max_results=$maxresults";
$url .= "&max_results=$maxresults";

$params .= "&oauth_consumer_key=$O1_Consumer_Key";
$url .= "&oauth_consumer_key=$O1_Consumer_Key";

$params .= "&oauth_nonce=poob";
$url .= "&oauth_nonce=poob";

$params .= "&oauth_signature_method=HMAC-SHA1";
$url .= "&oauth_signature_method=HMAC-SHA1";

$timestamp = time();
$params .= "&oauth_timestamp=$timestamp";
$url .= "&oauth_timestamp=$timestamp";

$params .= "&oauth_version=1.0";
$url .= "&oauth_version=1.0";

$params .= "&page_number=$page";
$url .= "&page_number=$page";

$params .= "&search_expression=$searchQuery";
$url .= "&search_expression=$searchQuery";

//creating the signature base which will be turned into a hash value
$signatureBase = "GET&";
$signatureBase .= rawurlencode($mainUrl) . "&";
$signatureBase .= rawurlencode($params);

//create the final hash value and grant it its rightful place in the URL. The & at the end of the secret is neccessary: an Access Secret goes after it if needed 
$signature = hash_hmac("sha1", $signatureBase, "$O1_Consumer_Secret&", true);
$base64Signature = base64_encode($signature);
$url .= "&oauth_signature=" . rawurlencode($base64Signature);

curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$apiresponse = curl_exec($ch);

if ($apiresponse === false) {
    sendLogMessage(
        "Food search API cURL failed: " . curl_error($ch),
        "ERROR",
        "backend-api"
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food search API request failed."));
    curl_close($ch);
    exit();
}

curl_close($ch);

if ($apiresponse == null || trim($apiresponse) === "") {
    sendLogMessage(
        "Food search API returned an empty response for search query: " . $searchQuery,
        "ERROR",
        "backend-api"
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food search API returned no data."));
    exit();
}

$decodedResponse = json_decode($apiresponse, true);

if ($decodedResponse === null) {
    sendLogMessage(
        "Food search API returned invalid JSON for search query: " . $searchQuery,
        "ERROR",
        "backend-api"
    );

    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Food search API returned invalid data."));
    exit();
}

//$jsonresponse = json_encode($apiresponse);

//If everythin succeeds, raw JSON text from the food search API should be echoed
header('Content-Type: application/json');
echo $apiresponse;

?>