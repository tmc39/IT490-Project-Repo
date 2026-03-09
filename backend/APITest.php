<?php

//Include the file that stores needed keys. This should hopefully prevent my super secret keys from being leaked on Github.
//uses key varaibles called $O1_Consumer_Key (ID key) and $O1_Consumer_Secret (secret key), used for FatSecret's oauth 1.0 URL-based authentication
require '../../../BigFatKeys.php';


// For testing. Put the word you want to search in the URL parameters (i.e. localhost/backend/APITest.php?search=bagel)
$searchQuery = $_GET['search'];

//how many results will be returned from the search
$maxresults = 5;

if($searchQuery == null){
    $searchQuery = "bagel";
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

//If everythin succeeds, raw JSON text from the food search API should be printed onto the screen
echo $apiresponse;

?>