<?php
$student_id = "2345"; // Example student ID

// ESP8266 IP address
$esp8266_ip = "http://192.168.1.13/captureFingerprint?student_id=" . $student_id;

// Use cURL to make an HTTP GET request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $esp8266_ip);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo "Fingerprint capture triggered for Student ID: " . $student_id . "<br>";
    echo "Response: " . $response;
}

curl_close($ch);
