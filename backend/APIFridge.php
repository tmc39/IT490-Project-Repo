<?php
require_once('/home/it490/Desktop/IT490-Project-Repo/backend/BigFatKeys.php');

function identifyFridgeItems($base64Image) {
    // 1. Get OAuth2 Token from FatSecret
    // Uses the client_id and client_secret from your BigFatKeys.php
    $tokenUrl = "https://oauth.fatsecret.com/connect/token";
    $id = FATSECRET_CLIENT_ID;
    $secret = FATSECRET_CLIENT_SECRET;

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
        return ["status" => "error", "message" => "Failed to get API Token"];
    }

    // 2. Send Image to FatSecret Recognize API
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

    // 3. Return the real data
    if (isset($apiResult['food_recognition'])) {
        return [
            "status" => "success",
            "food_name" => $apiResult['food_recognition']['suggestions']['suggestion'][0]['food_name'] ?? "Unknown Food",
            "data" => $apiResult
        ];
    }

    return ["status" => "error", "message" => "API could not identify image", "raw" => $apiResult];
}
