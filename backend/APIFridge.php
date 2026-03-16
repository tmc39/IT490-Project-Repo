<?php
// backend/APIFridge.php

// 1. FIX PATH: Points exactly to where your 'cat' command showed the file
$keyPath = '/home/it490/Desktop/IT490-Project-Repo/backend/BigFatKeys.php';

if (file_exists($keyPath)) {
    require_once($keyPath);
} else {
    echo "CRITICAL ERROR: BigFatKeys.php not found at: $keyPath" . PHP_EOL;
}

function identifyFridgeItems($base64Image) {
    // 2. USE YOUR VARIABLES: Bringing them into the function scope
    global $fatSecretKey, $fatSecretSecret;

    if (!isset($fatSecretKey) || !isset($fatSecretSecret)) {
        return [
            "status" => "error", 
            "message" => "API Credentials ($fatSecretKey/$fatSecretSecret) are not set in BigFatKeys.php"
        ];
    }

    // 3. GET OAUTH2 TOKEN
    $tokenUrl = "https://oauth.fatsecret.com/connect/token";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=basic");
    curl_setopt($ch, CURLOPT_USERPWD, "$fatSecretKey:$fatSecretSecret");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $tokenResponse = json_decode(curl_exec($ch), true);
    $accessToken = $tokenResponse['access_token'] ?? null;
    curl_close($ch);

    if (!$accessToken) {
        return [
            "status" => "error", 
            "message" => "Failed to get API Token. Check your Key and Secret.",
            "debug" => $tokenResponse
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

    // 5. RETURN THE DATA
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
