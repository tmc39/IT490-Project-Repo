<?php
// backend/APIFridge.php

function identifyFridgeItems($base64Image) {
    // 1. LOAD KEYS DIRECTLY INTO THE FUNCTION SCOPE
    // This guarantees the variables exist right here, right now.
    $keyPath = '/home/it490/Desktop/IT490-Project-Repo/backend/BigFatKeys.php';
    
    if (!file_exists($keyPath)) {
        return ["status" => "error", "message" => "CRITICAL: BigFatKeys.php not found at $keyPath"];
    }
    
    // We use require() instead of require_once() inside functions for daemon scripts
    require($keyPath); 

    // Verify the variables from your screenshot loaded successfully
    if (empty($fatSecretKey) || empty($fatSecretSecret)) {
        return [
            "status" => "error", 
            "message" => "Keys loaded but empty! Check BigFatKeys.php syntax."
        ];
    }

    // 2. GET OAUTH2 TOKEN
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

    // 3. SEND IMAGE TO RECOGNIZE API
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

    // 4. RETURN THE REAL DATA TO THE FRONTEND
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
?>
