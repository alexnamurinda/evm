<?php
// admin_status.php - Checks verification status from ESP32
session_start();

$esp32_ip = "192.168.1.13"; // ESP32 IP address

// Ensure we're checking for an admin that hasn't been fully authenticated yet
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "unauthorized";
    exit;
}

// If admin is already fully authenticated, don't check again
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    echo "success";
    exit;
}

// Get status from ESP32
$url = "http://{$esp32_ip}/admin_verification_status";
$response = @file_get_contents($url);

if ($response === false) {
    echo "pending";
} else {
    $status = trim($response);
    if ($status === "success") {
        // Only set admin_authenticated to true when ESP32 confirms success
        $_SESSION['admin_authenticated'] = true;
        echo "success";
    } else if ($status === "failed") {
        echo "failed";
    } else {
        echo "pending";
    }
}
?>