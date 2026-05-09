<?php

/*
/   This script interfaces with FatSecret's recipe search and returns the requested results data in JSON format.
/   parameters are put into this script via URL parameters
/
/   https://platform.fatsecret.com/docs/v3/recipes.search
*/


//Include the file that stores needed keys. This should hopefully prevent my super secret keys from being leaked on Github.
//This script usess key varaibles called $O1_Consumer_Key (ID key) and $O1_Consumer_Secret (secret key), used for FatSecret's oauth 1.0 URL-based authentication
require 'BigFatKeys.php';


// access the URL parameters provided. If they are null, set placeholder values
$searchQuery = $_GET['search'];
if($searchQuery == null){
    $searchQuery = "bagel";
}

$maxresults = $_GET['results'];
if($maxresults == null){
    $maxresults = 10;
}

$page = $_GET['page'];
if($page == null){
    $page = 0;
}

$ch = curl_init();


//https://platform.fatsecret.com/docs/guides/authentication/oauth1


//adds the given search query into the curl session's url
//mainUrl is used for signature encoding
$mainUrl = "https://platform.fatsecret.com/rest/recipes/search/v3";
$url = 'https://platform.fatsecret.com/rest/recipes/search/v3?';


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
$url .="&search_expression=$searchQuery";


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

unset($ch);

//$jsonresponse = json_encode($apiresponse);

//If everythin succeeds, raw JSON text from the food search API should be echoed
header('Content-Type: application/json');
echo $apiresponse;

?>