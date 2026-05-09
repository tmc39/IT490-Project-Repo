// frontend/js/fridgescanner.js

function scanFridgeImage() {
    const fileInput = document.getElementById('fridgeImage');
    const resultsDiv = document.getElementById('resultsDiv');

    // Make sure they actually selected a file
    if (fileInput.files.length === 0) {
        resultsDiv.innerHTML = "<p style='color:red;'>Please select an image first!</p>";
        return false;
    }

    const file = fileInput.files[0];
    const reader = new FileReader();

    // Show a loading message
    resultsDiv.innerHTML = "<p>Scanning image with AI... Please wait.</p>";

    // This runs once the image is fully loaded into memory
    reader.onload = function(e) {
        const base64String = e.target.result;

        // Prepare the AJAX request to your new submit script
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/frontend/lib/submitfridge.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.status === "success") {
                        // Print the FatSecret nutrition data dynamically
                        resultsDiv.innerHTML = `
                            <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;">
                                <h3 style="margin-top: 0;">Scan Successful!</h3>
                                <p><strong>Food Identified:</strong> ${response.food_name}</p>
                                <p><strong>Nutrition Info:</strong> ${response.calories}</p>
                            </div>
                        `;
                    } else {
                        resultsDiv.innerHTML = `<p style='color:red;'>Error: ${response.message}</p>`;
                    }
                } catch (error) {
                    resultsDiv.innerHTML = `<p style='color:red;'>Failed to parse server response.</p><pre>${xhr.responseText}</pre>`;
                }
            } else {
                resultsDiv.innerHTML = `<p style='color:red;'>Server returned status ${xhr.status}</p>`;
            }
        };

        // Send the Base64 image string to PHP
        xhr.send("image=" + encodeURIComponent(base64String));
    };

    // Trigger the file read
    reader.readAsDataURL(file);
    return false;
}
