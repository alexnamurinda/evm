
<?php
// admin_verify.php - Sends verification request to ESP32
$esp32_ip = "192.168.1.5"; // Get the ESP32 IP from the connected clients list or set manually

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

// Send request to ESP32
$url = "http://{$esp32_ip}/admin_verify";
$response = @file_get_contents($url);

if ($response === false) {
    echo "error";
} else {
    echo "verification_started";
}
?>
