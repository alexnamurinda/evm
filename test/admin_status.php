<?php
// admin_status.php - Checks verification status from ESP32
session_start();

$esp32_ip = "192.168.1.13"; // Get the ESP32 IP from the connected clients list or set manually

// Check if we already have a successful response stored in session
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    echo "success";
    exit;
}

// Try to get ESP32 IP if not set manually
if (empty($esp32_ip)) {
    // Check if it's stored in session or a config file
    if (isset($_SESSION['esp32_ip'])) {
        $esp32_ip = $_SESSION['esp32_ip'];
    } else {
        // You might want to implement a way to discover the ESP32 IP
        // or hardcode it if it has a static IP
        $esp32_ip = $_SERVER['REMOTE_ADDR']; // This is a fallback and might not work in all setups
    }
}

// Get status from ESP32
$url = "http://{$esp32_ip}/admin_verification_status";
$response = @file_get_contents($url);

if ($response === false) {
    echo "pending";
} else {
    // If verification was successful, set session variable
    if (trim($response) === "success") {
        $_SESSION['admin_authenticated'] = true;
        echo "success";
    } else if (trim($response) === "failed") {
        echo "failed";
    } else {
        echo "pending";
    }
}
?>