<?php
session_start();
require '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentID = $_POST['studentID'];
    $password = $_POST['password'];

    if ($studentID === 'admin' && $password === 'admin') {
        // Admin login detected, initiate fingerprint verification
        $_SESSION['admin'] = true;
        $_SESSION['student_id'] = 'admin';
        $_SESSION['student_name'] = 'Administrator';
        
        // Redirect to fingerprint verification page
        header("Location: admin_login.php");
        exit();
    } else {
        try {
            // Fetch student record
            $stmt = $conn->prepare("SELECT student_name, course, password FROM students WHERE student_id = ?");
            $stmt->execute([$studentID]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student && password_verify($password, $student['password'])) {
                // Store student details in session
                $_SESSION['student_id'] = $studentID;
                $_SESSION['student_name'] = $student['student_name'];
                $_SESSION['course'] = $student['course'];

                // Redirect to student dashboard
                header("Location: ../student_dashboard.php");
                exit();
            } else {
                $error = "Invalid Student ID or Password!";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student/Admin Login</title>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($_GET['error'])) echo "<p class='error'>{$_GET['error']}</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="studentID">Student ID:</label>
                <input type="text" name="studentID" id="studentID" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
            <a href="#" style="color: blue; text-decoration: none;">Forgot password</a>
        </form>
    </div>
</body>
</html>
