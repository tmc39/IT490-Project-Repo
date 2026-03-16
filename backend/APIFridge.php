<?php
// backend/APIFridge.php
$keyPath = '/home/it490/Desktop/IT490-Project-Repo/backend/BigFatKeys.php';

if (file_exists($keyPath)) {
    require_once($keyPath);
}

function identifyFridgeItems($base64Image) {
    // You must tell the function to use the variables from BigFatKeys.php
    global $fatSecretKey, $fatSecretSecret;

    // Debugging check: if these are empty, the error message will tell us
    if (empty($fatSecretKey) || empty($fatSecretSecret)) {
        return [
            "status" => "error", 
            "message" => "API Credentials are empty in BigFatKeys.php"
        ];
    }

    // Use the variables for the OAuth2 call
    $id = $fatSecretKey;
    $secret = $fatSecretSecret;

    // --- OAuth2 Token Logic ---
    $tokenUrl = "https://oauth.fatsecret.com/connect/token";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=basic");
    curl_setopt($ch, CURLOPT_USERPWD, "$id:$secret");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $tokenResponse = json_decode(curl_exec($ch), true);
    $accessToken = $tokenResponse['access_token'] ?? null;
    curl_close($ch);

    if (!$accessToken) {
        return ["status" => "error", "message" => "Failed to get API Token from FatSecret"];
    }

    // --- API Recognition Logic ---
    $apiUrl = "https://platform.fatsecret.com/rest/server.api";
    $postData = [
        'method' => 'food.recognize',
        'image' => $base64Image,
        'format' => 'json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $apiResult = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($apiResult['food_recognition']['suggestions']['suggestion'])) {
        $food = $apiResult['food_recognition']['suggestions']['suggestion'][0];
        return [
            "status" => "success",
            "food_name" => $food['food_name'],
            "calories" => $food['calories'],
            "message" => "API identification successful"
        ];
    }

    return ["status" => "error", "message" => "Food not recognized", "raw" => $apiResult];
}
