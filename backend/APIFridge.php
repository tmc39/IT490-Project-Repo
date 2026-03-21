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

    $id = trim($fatSecretKey);
    $secret = trim($fatSecretSecret);

    // 1. Get OAuth2 Token
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
            "message" => "Invalid Client: Check your Key/Secret on FatSecret Platform",
            "debug" => $tokenResponse
        ];
    }

    // 2. NEW V2 IMAGE RECOGNITION API
    $apiUrl = "https://platform.fatsecret.com/rest/image-recognition/v2";
    
    // V2 uses 'image_b64' inside a raw JSON payload
    $postData = [
        'image_b64' => $base64Image,
        'region' => 'US',
        'language' => 'en'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    // Must be json_encoded for V2
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json" // V2 requires application/json
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $apiResult = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // 3. PARSE V2 RESPONSE STRUCTURE
    if (isset($apiResult['food_response'][0])) {
        $food = $apiResult['food_response'][0];
        
        // V2 nests calories deeply inside the 'eaten' -> 'total_nutritional_content' array
        $calories = "N/A";
        if (isset($food['eaten']['total_nutritional_content']['calories'])) {
            $calories = $food['eaten']['total_nutritional_content']['calories'] . " kcal";
        }

        return [
            "status" => "success",
            "food_name" => $food['food_entry_name'],
            "calories" => $calories,
            "message" => "V2 Image Recognition Successful!"
        ];
    }

    // Check for specific V2 Error Codes (like 211 for 'No food item detected')
    if (isset($apiResult['error'])) {
        return ["status" => "error", "message" => "API Error: " . $apiResult['error']['message'], "raw" => $apiResult];
    }

    return ["status" => "error", "message" => "Food not recognized in image", "raw" => $apiResult];
}
?>
