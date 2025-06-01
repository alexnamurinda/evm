<?php
// Add no-cache headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$esp_ip = "192.168.1.9"; // ESP32 IP address

// Add a timeout to prevent hanging
$context = stream_context_create([
    'http' => [
        'timeout' => 5, // 5 second timeout
    ]
]);
 
$response = @file_get_contents("http://$esp_ip/enrollment_status", false, $context);

if ($response === FALSE) {
    echo "waiting"; // Keep retrying if there's no response
} else {
    // Log the response for debugging
    error_log("ESP32 Response: " . $response);
    
    // Return the raw response without additional processing
    echo trim($response);
}
?>