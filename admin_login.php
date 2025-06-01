<?php
// admin_login.php - The initial admin login page
session_start();

// Verify that the user is coming from the proper login process
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Redirect unauthorized access back to login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVM Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        #status-message {
            text-align: center;
            margin-top: 15px;
        }
        .loading {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <h3>EVM System</h3>
            </div>
            <h2>Admin Login</h2>
           
            <div id="loading" class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p id="status-text">Please place your finger on the scanner...</p>
            </div>
           
            <div id="status-message"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Flag to track verification status
            var verificationInProgress = false;
            var verificationComplete = false;
            
            // Automatically start fingerprint verification on page load
            initiateFingerprint();
            
            function initiateFingerprint() {
                // Reset flags
                verificationInProgress = true;
                verificationComplete = false;
                
                $('#status-text').text('Please place your finger on the scanner...');
                $('#status-message').empty();
               
                // Send verification request to ESP32 only once
                $.ajax({
                    url: 'admin_verify.php',
                    type: 'GET',
                    cache: false,
                    success: function(data) {
                        if (data.trim() === "verification_started") {
                            // Start polling for verification status
                            checkVerificationStatus();
                        } else {
                            $('#status-message').html('<div class="alert alert-danger">Failed to start verification. Please try again.</div>');
                            verificationInProgress = false;
                            addRetryButton();
                        }
                    },
                    error: function() {
                        $('#status-message').html('<div class="alert alert-danger">Error connecting to verification service.</div>');
                        verificationInProgress = false;
                        addRetryButton();
                    }
                });
            }
           
            function checkVerificationStatus() {
                // Stop polling if verification is already complete
                if (verificationComplete || !verificationInProgress) {
                    return;
                }
                
                $.ajax({
                    url: 'admin_status.php',
                    type: 'GET',
                    cache: false,
                    success: function(data) {
                        var result = data.trim();
                        
                        if (result === 'success') {
                            // Set verification as complete to stop further polling
                            verificationComplete = true;
                            verificationInProgress = false;
                            
                            // Update UI with success message
                            $('#status-message').html('<div class="alert alert-success">Admin verified! Redirecting...</div>');
                            $('#loading .spinner-border').hide();
                            $('#status-text').text('Verification successful!');
                            
                            // Redirect after the specified timeout (1500ms = 1.5 seconds)
                            // This timeout gives users a chance to see the success message
                            setTimeout(function() {
                                window.location.href = 'admin_dashboard.php';
                            }, 1500);
                            
                        } else if (result === 'failed') {
                            // Verification failed
                            verificationInProgress = false;
                            $('#status-message').html('<div class="alert alert-danger">Verification failed! Please try again.</div>');
                            $('#status-text').text('Verification failed.');
                            addRetryButton();
                            
                        } else if (result === 'pending') {
                            // Keep polling if verification is still pending
                            $('#status-text').text('Waiting for fingerprint verification...');
                            setTimeout(checkVerificationStatus, 1000);
                            
                        } else {
                            // Unknown response
                            verificationInProgress = false;
                            $('#status-message').html('<div class="alert alert-warning">Received unexpected response. Please try again.</div>');
                            $('#status-text').text('Verification error.');
                            addRetryButton();
                        }
                    },
                    error: function() {
                        verificationInProgress = false;
                        $('#status-message').html('<div class="alert alert-danger">Error checking verification status.</div>');
                        $('#status-text').text('Connection error.');
                        addRetryButton();
                    }
                });
            }
            
            function addRetryButton() {
                // Add retry button when verification fails
                $('#status-message').append('<div class="text-center mt-3"><button id="retry-button" class="btn btn-primary">Retry</button></div>');
                
                // Set up retry button handler
                $('#retry-button').click(function() {
                    initiateFingerprint();
                });
            }
        });
    </script>
</body>
</html>