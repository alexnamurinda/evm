<?php
// admin_verify.php - Sends verification request to ESP32
session_start();

// Verify the user is coming from the proper login process
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "unauthorized";
    exit;
}

// Reset any previous authentication status
if (isset($_SESSION['admin_authenticated'])) {
    unset($_SESSION['admin_authenticated']);
}

$esp32_ip = "192.168.1.13"; // ESP32 IP address

// Send request to ESP32
$url = "http://{$esp32_ip}/admin_verify";
$response = @file_get_contents($url);

if ($response === false) {
    echo "error";
} else {
    echo "verification_started";
}
?>