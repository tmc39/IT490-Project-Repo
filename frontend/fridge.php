<?php
session_start();
// Pointing to the actual integration folder
require_once __DIR__ . '/../integration/lib/rabbitMQClient.php';

if (empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$results = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["fridge_image"])) {
    $imageFile = $_FILES["fridge_image"]["tmp_name"];
    $imageData = base64_encode(file_get_contents($imageFile));
    
    $request = [
        'type' => 'fridge_scan',
        'username' => $_SESSION["username"],
        'image_data' => $imageData
    ];

    try {
        // Using the standard function name from your library
        $results = createClientRequest($request); 
    } catch (Exception $e) {
        $error = "API Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Fridge Scanner</title></head>
<body>
    <h1>Fridge Scanner</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="fridge_image" required>
        <button type="submit">Identify Food</button>
    </form>
    <?php if ($results): ?>
        <h3>Results:</h3>
        <pre><?php print_r($results); ?></pre>
    <?php endif; ?>
</body>
</html>
