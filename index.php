<?php
require 'db_creation.php';
require 'db_connection.php';
session_start();

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Check if we have the X-Forwarded-For header (used by proxies like ngrok)
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // This will be a comma-separated list of IPs, the first one is the real client IP
    $ip_address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
} else {
    // Use REMOTE_ADDR if no X-Forwarded-For header is available
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

// Check if the user is in the lockout period
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $remaining_time = $_SESSION['lockout_time'] - time();
    $error = "Too many failed attempts. Try again in " . $remaining_time . " seconds.";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $studentID = trim($_POST['studentID']);
        $password = trim($_POST['password']);

        // Reset attempts if lockout expired
        if (isset($_SESSION['lockout_time']) && time() >= $_SESSION['lockout_time']) {
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lockout_time']);
        }

        if ($_SESSION['login_attempts'] >= 3) { // Lock user after 3 failed attempts
            $_SESSION['lockout_time'] = time() + 300; // 5-minute lockout
            $error = "Too many failed attempts. Try again in 5 minutes.";
        } else {
            if ($studentID === 'admin1' && $password === '321@admin@123') {
                // Admin login success
                $_SESSION['admin'] = true;
                $_SESSION['student_id'] = 'admin';
                $_SESSION['student_name'] = 'Administrator';
                $_SESSION['login_attempts'] = 0; // Reset attempts

                // Store successful login attempt
                $stmt = $conn->prepare("INSERT INTO login_attempts (student_id, ip_address, status) VALUES (?, ?, 'success')");
                $stmt->execute([$studentID, $ip_address]);

                header("Location: capture_fing.php");
                exit();
            } else {
                try {
                    // Fetch student record
                    $stmt = $conn->prepare("SELECT student_name, course, password FROM students WHERE student_id = ?");
                    $stmt->execute([$studentID]);
                    $student = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($student && password_verify($password, $student['password'])) {
                        // Successful login
                        $_SESSION['student_id'] = $studentID;
                        $_SESSION['student_name'] = $student['student_name'];
                        $_SESSION['course'] = $student['course'];
                        $_SESSION['login_attempts'] = 0; // Reset attempts

                        // Store successful login attempt
                        $stmt = $conn->prepare("INSERT INTO login_attempts (student_id, ip_address, status) VALUES (?, ?, 'success')");
                        $stmt->execute([$studentID, $ip_address]);

                        header("Location: student_dashboard.php");
                        exit();
                    } else {
                        $_SESSION['login_attempts']++;

                        // Store failed login attempt
                        $stmt = $conn->prepare("INSERT INTO login_attempts (student_id, ip_address, status) VALUES (?, ?, 'failed')");
                        $stmt->execute([$studentID, $ip_address]);

                        $error = "Invalid Student ID or Password!";
                    }
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <style>
        /* General Reset */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url(IMAGES/back.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
        }

        /* body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(179, 176, 176, 0.54);
            z-index: -1;
        } */

        .login-container {
            background: rgba(70, 71, 140, 0.5);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
            animation: fadeIn 1s ease-in-out;
        }

        h2 {
            color: #fff;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            color: #fff;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }

        input:focus {
            border: 2px solid #fff;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #353670;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s ease-in-out;
            margin-bottom: 10px
        }

        .btn:hover {
            background: rgb(75, 77, 165);
            transform: scale(1.05);
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        a:hover {
            color: rgb(75, 77, 165);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Student Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="studentID">Student ID:</label>
                <input type="text" name="studentID" id="studentID" required
                    <?php echo (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) ? 'disabled' : ''; ?>>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required
                    <?php echo (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) ? 'disabled' : ''; ?>>
            </div>

            <button type="submit" class="btn"
                <?php echo (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) ? 'disabled' : ''; ?>>
                Login
            </button>
            <a href="#" style="color: white; text-decoration: none;">Forgot password</a>
        </form>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const form = document.getElementById("loginForm");

                form.addEventListener("submit", function() {
                    setTimeout(() => {
                        form.reset(); // Clears all fields
                    }, 100); // Delay to ensure PHP processes first
                });
            });
        </script>
    </div>
</body>

</html>