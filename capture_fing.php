<?php
// capture_fing.php - The fingerprint verification page
session_start();

// Verify that the user is coming from the proper login process
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Redirect unauthorized access back to login page
    header("Location: index.php");
    exit();
}

// Reset any previous authentication status
if (isset($_SESSION['admin_authenticated'])) {
    unset($_SESSION['admin_authenticated']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVM Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --background-color: #f8f9fa;
        }
        
        body {
            background-color: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 90%;
            max-width: 450px;
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo h3 {
            color: var(--secondary-color);
            font-weight: 700;
            margin: 0;
            position: relative;
            display: inline-block;
        }
        
        .logo h3:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background-color: var(--primary-color);
            bottom: -10px;
            left: 25%;
            border-radius: 2px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .scanner-frame {
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .loading {
            text-align: center;
        }
        
        .spinner-container {
            margin-bottom: 1rem;
            position: relative;
            width: 60px;
            height: 60px;
            margin: 0 auto 1.5rem;
        }
        
        .spinner-outer {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(52, 152, 219, 0.2);
            border-radius: 50%;
            animation: spin 2s linear infinite;
            position: absolute;
        }
        
        .spinner-inner {
            width: 60px;
            height: 60px;
            border: 4px solid transparent;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
            position: absolute;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fingerprint-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        #status-text {
            font-size: 1.1rem;
            color: var(--secondary-color);
            margin: 1rem 0;
            font-weight: 500;
        }
        
        #status-message {
            margin-top: 1.5rem;
        }
        
        .alert {
            border-radius: 8px;
            font-weight: 500;
            padding: 1rem;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.15);
            color: #1d8348;
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.15);
            color: #b03a2e;
        }
        
        .alert-warning {
            background-color: rgba(241, 196, 15, 0.15);
            color: #b7950b;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 6px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.1);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(52, 152, 219, 0.2);
        }
        
        .btn-primary:active {
            transform: translateY(1px);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        /* Success animation */
        .success-checkmark {
            display: none;
            margin: 0 auto;
            width: 80px;
            height: 80px;
            position: relative;
        }
        
        .success-checkmark .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid var(--success-color);
        }
        
        .success-checkmark .check-icon::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            border-radius: 50%;
            background-color: rgba(46, 204, 113, 0.1);
            animation: fill-success 0.4s ease-in-out 0.3s forwards;
        }
        
        .success-checkmark .check-icon::after {
            content: "";
            width: 25px;
            height: 50px;
            border-right: 4px solid var(--success-color);
            border-top: 4px solid var(--success-color);
            position: absolute;
            top: 14px;
            left: 29px;
            transform: scaleX(-1) rotate(135deg);
            transform-origin: 0 0;
            animation: checkmark 0.6s ease 0.3s forwards;
            opacity: 0;
        }
        
        @keyframes checkmark {
            0% { height: 0; width: 0; opacity: 0; }
            40% { height: 0; width: 25px; opacity: 1; }
            100% { height: 50px; width: 25px; opacity: 1; }
        }
        
        @keyframes fill-success {
            0% { width: 0; height: 0; }
            100% { width: 76px; height: 76px; }
        }
        
        /* Error animation */
        .error-x {
            display: none;
            margin: 0 auto;
            width: 80px;
            height: 80px;
            position: relative;
        }
        
        .error-x .x-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid var(--error-color);
        }
        
        .error-x .x-icon::before, .error-x .x-icon::after {
            content: "";
            position: absolute;
            height: 4px;
            width: 40px;
            background-color: var(--error-color);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .error-x .x-icon::before {
            transform: translate(-50%, -50%) rotate(45deg);
            animation: x-animation-1 0.7s ease forwards;
            opacity: 0;
        }
        
        .error-x .x-icon::after {
            transform: translate(-50%, -50%) rotate(-45deg);
            animation: x-animation-2 0.7s ease forwards;
            opacity: 0;
        }
        
        @keyframes x-animation-1 {
            0% { opacity: 0; transform: translate(-50%, -50%) rotate(45deg) scale(0.5); }
            50% { opacity: 1; transform: translate(-50%, -50%) rotate(45deg) scale(1.2); }
            100% { opacity: 1; transform: translate(-50%, -50%) rotate(45deg) scale(1); }
        }
        
        @keyframes x-animation-2 {
            0% { opacity: 0; transform: translate(-50%, -50%) rotate(-45deg) scale(0.5); }
            50% { opacity: 1; transform: translate(-50%, -50%) rotate(-45deg) scale(1.2); }
            100% { opacity: 1; transform: translate(-50%, -50%) rotate(-45deg) scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <!-- <h3>EVM SYSTEM</h3> -->
            </div>
            <h2>Admin Authentication</h2>
            
            <div class="scanner-frame">
                <div id="loading" class="loading">
                    <div class="fingerprint-icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="spinner-container">
                        <div class="spinner-outer"></div>
                        <div class="spinner-inner"></div>
                    </div>
                    <p id="status-text">Please place your finger on the scanner</p>
                </div>
                
                <div class="success-checkmark" id="success-animation">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <div class="error-x" id="error-animation">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            
            <div id="status-message"></div>
            
            <footer>
                Electronic Voting Machine Administration System
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Flag to track verification status
            var verificationInProgress = false;
            var verificationComplete = false;
            
            // Automatically start fingerprint verification on page load
            initiateFingerprint();
            
            function initiateFingerprint() {
                // Reset flags and UI
                verificationInProgress = true;
                verificationComplete = false;
                
                // Reset animations
                $('#loading').show();
                $('#success-animation').hide();
                $('#error-animation').hide();
                
                $('#status-text').text('Please place your finger on the scanner');
                $('#status-message').empty();
                
                // Send verification request to ESP32
                $.ajax({
                    url: 'http://192.168.1.5/admin_verify',
                    type: 'GET',
                    cache: false,
                    success: function(data) {
                        if (data.trim() === "verification_started") {
                            // Start polling for verification status
                            checkVerificationStatus();
                        } else if (data.trim() === "unauthorized") {
                            window.location.href = 'index.php';
                        } else {
                            showError('Failed to start verification. Please try again.');
                        }
                    },
                    error: function() {
                        showError('Error connecting to verification service.');
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
                            showSuccess();
                        } else if (result === 'failed') {
                            showError('Fingerprint not recognized. Authentication failed.');
                        } else if (result === 'unauthorized') {
                            window.location.href = 'login.php';
                        } else if (result === 'pending') {
                            // Keep polling if verification is still pending
                            $('#status-text').text('Scanning fingerprint...');
                            setTimeout(checkVerificationStatus, 1000);
                        } else {
                            showError('Received unexpected response. Please try again.');
                        }
                    },
                    error: function() {
                        showError('Error checking verification status.');
                    }
                });
            }
            
            function showSuccess() {
                // Set verification as complete to stop further polling
                verificationComplete = true;
                verificationInProgress = false;
                
                // Hide loading, show success animation
                $('#loading').hide();
                $('#success-animation').show();
                
                // Update UI with success message
                $('#status-text').text('Verification successful!');
                $('#status-message').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Admin verified! Redirecting to dashboard...</div>');
                
                // Redirect after the specified timeout (2000ms = 2 seconds)
                setTimeout(function() {
                    window.location.href = 'admin.php';
                }, 2000);
            }
            
            function showError(message) {
                verificationInProgress = false;
                
                // Hide loading, show error animation
                $('#loading').hide();
                $('#error-animation').show();
                
                $('#status-text').text('Verification failed');
                $('#status-message').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + message + '</div>');
                addRetryButton();
            }
            
            function addRetryButton() {
                // Add retry button when verification fails
                $('#status-message').append('<div class="text-center mt-3"><button id="retry-button" class="btn btn-primary">Try Again</button></div>');
                
                // Set up retry button handler
                $('#retry-button').click(function() {
                    initiateFingerprint();
                });
            }
        });
    </script>
</body>
</html>