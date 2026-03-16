<?php
session_start();

// Enable error reporting so we can see what's happening
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the path to the library is correct
require_once(__DIR__ . '/../integration/lib/rabbitMQLib.inc');

if (empty($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$results = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["fridge_image"])) {
    $imageFile = $_FILES["fridge_image"]["tmp_name"];
    
    // Check if the file was actually uploaded
    if (file_exists($imageFile)) {
        $imageData = base64_encode(file_get_contents($imageFile));
        
        $request = [
            'type' => 'fridge_scan',
            'username' => $_SESSION["username"],
            'image_data' => $imageData
        ];

        try {
            /** * FIXED LOGIC: 
             * 1. Create the client object pointing to your .ini
             * 2. Use the 'testServer' instance defined in your .ini
             */
            $client = new rabbitMQClient(__DIR__ . "/../integration/scripts/testRabbitMQ.ini", "testServer");
            $results = $client->send_request($request);
            
        } catch (Exception $e) {
            $error = "RabbitMQ Error: " . $e->getMessage();
        }
    } else {
        $error = "No file uploaded or file path invalid.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fridge Scanner</title>
</head>
<body>
    <h1>Fridge Scanner</h1>
    
    <?php if ($error): ?>
        <p style="color:red; font-weight:bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="fridge_image">Select Fridge Image:</label><br>
        <input type="file" name="fridge_image" id="fridge_image" required>
        <br><br>
        <button type="submit">Identify Food</button>
    </form>

    <hr>

    <?php if ($results): ?>
        <h3>API Identification Results:</h3>
        <pre><?php print_r($results); ?></pre>
    <?php else: ?>
        <p>Upload an image to see results from the backend.</p>
    <?php endif; ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
