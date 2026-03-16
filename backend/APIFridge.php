<?php
// backend/APIFridge.php

function identifyFridgeItems($base64Image) {
    if (!function_exists('curl_init')) {
        return ["status" => "error", "message" => "PHP CURL missing."];
    }

    $keyPath = '/home/it490/Desktop/IT490-Project-Repo/backend/BigFatKeys.php';
    if (!file_exists($keyPath)) {
        return ["status" => "error", "message" => "BigFatKeys.php not found."];
    }
    
    require($keyPath); 

    // trim() handles any accidental spaces from the keys file
    $id = trim($fatSecretKey);
    $secret = trim($fatSecretSecret);

    // 1. Get OAuth2 Token
    $tokenUrl = "https://oauth.fatsecret.com/connect/token";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=basic");
    curl_setopt($ch, CURLOPT_USERPWD, "$id:$secret"); // Standard Basic Auth
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $tokenResponse = json_decode(curl_exec($ch), true);
    $accessToken = $tokenResponse['access_token'] ?? null;
    curl_close($ch);

    if (!$accessToken) {
        return [
            "status" => "error", 
            "message" => "Invalid Client: Check your Key/Secret on FatSecret Platform",
            "debug" => $tokenResponse
        ];
    }

    // 2. Send Image to Recognize API
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
            "calories" => $food['calories'] ?? "N/A",
            "message" => "Successfully identified food!"
        ];
    }

    return ["status" => "error", "message" => "Food not recognized", "raw" => $apiResult];
}
?>
