<?php
// backend/APIFridge.php

$keyPath = 'BigFatKeys.php';

if (file_exists($keyPath)) {
    require_once($keyPath);
} else {
    // This will print in your testRabbitMQServer terminal
    echo "CRITICAL ERROR: BigFatKeys.php not found at: $keyPath" . PHP_EOL;
}

function identifyFridgeItems($base64Image) {
    // 2. CHECK CONSTANTS: Use defined() to prevent Fatal Errors if keys are missing
    if (!defined('FATSECRET_CLIENT_ID') || !defined('FATSECRET_CLIENT_SECRET')) {
        return [
            "status" => "error", 
            "message" => "API Credentials missing from BigFatKeys.php"
        ];
    }

    $id = FATSECRET_CLIENT_ID;
    $secret = FATSECRET_CLIENT_SECRET;

    // 3. GET OAUTH2 TOKEN
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
        return [
            "status" => "error", 
            "message" => "Failed to get API Token. Check your Client ID/Secret."
        ];
    }

    // 4. SEND IMAGE TO RECOGNIZE API
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $apiResult = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // 5. PARSE AND RETURN DATA
    // FatSecret returns an array of suggestions
    if (isset($apiResult['food_recognition']['suggestions']['suggestion'])) {
        $topSuggestion = $apiResult['food_recognition']['suggestions']['suggestion'][0];
        return [
            "status" => "success",
            "food_name" => $topSuggestion['food_name'] ?? "Unknown Food",
            "calories" => $topSuggestion['calories'] ?? "N/A",
            "message" => "Successfully identified food!"
        ];
    }

    return [
        "status" => "error", 
        "message" => "API could not identify image", 
        "debug" => $apiResult
    ];
}
