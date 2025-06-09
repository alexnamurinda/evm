<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ESP32 Status URL
$adminStatusUrl = "http://192.168.1.13/EEVM/admin_status.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Fingerprint Verification</title>
    <script>
        function checkFingerprint() {
            fetch("<?php echo $adminStatusUrl; ?>")
                .then(response => response.text())
                .then(status => {
                    if (status.trim() === "verified") {
                        window.location.href = "admin.php";
                    } else if (status.trim() === "failed") {
                        window.location.href = "login.php?error=Fingerprint authentication failed";
                    } else {
                        setTimeout(checkFingerprint, 3000); // Retry every 3 seconds
                    }
                })
                .catch(error => console.error("Error checking fingerprint:", error));
        }

        setTimeout(checkFingerprint, 3000);
    </script>
</head>
<body>
    <h2>Admin Fingerprint Verification</h2>
    <p>Please place your finger on the sensor...</p>
</body>
</html>
