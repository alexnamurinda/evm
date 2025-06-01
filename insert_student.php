<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentID = $_POST['studentID'];
    $name = $_POST['name'];
    $course = $_POST['course'];
    $program = $_POST['program'];

    // Check if the student already exists in the database
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$studentID]);
    $existingStudent = $stmt->fetch();

    // HTML Structure with Centered Content
    echo '<div style="display: flex; justify-content: center; align-items: center; height: 100vh; text-align: center; font-family: Arial, sans-serif;">';
    echo '<div style="border: 1px solid #ccc; border-radius: 10px; padding: 30px; width: 500px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">';

    // If the student already exists, display an error message
    if ($existingStudent) {
        echo '<h2 style="color: red; font-size: 24px;">❌ Student ID already enrolled!</h2>';
        echo '<button onclick="window.history.back()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 15px;">RETRY</button>';
    } else {
        // Send request to ESP8266 to capture fingerprint (Suppress warnings with '@')
        $response = @file_get_contents("http://192.168.1.9/capture_fingerprint");

        // Proceed only if fingerprint capture is successful
        if ($response === "success") {
            try {
                // Hash the student ID as the default password
                $hashedPassword = password_hash($studentID, PASSWORD_DEFAULT);

                // Insert student details into the database
                $stmt = $conn->prepare("INSERT INTO students (student_id, student_name, course, program, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$studentID, $name, $course, $program, $hashedPassword]);

                // Success message with OK button
                echo '<h2 style="color: green; font-size: 24px;">✅ Student enrolled!</h2>';
                echo '<button onclick="window.location.href=\'admin.php\'" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 15px;">OK</button>';
            } catch (PDOException $e) {
                die("<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>");
            }
        } else {
            // Error message with RETRY button
            echo '<h2 style="color: red; font-size: 24px;">❌ Error: Could not capture fingerprint.</h2>';
            echo '<button onclick="window.history.back()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 15px;">RETRY</button>';
        }
    }

    echo '</div>';
    echo '</div>';
}
?>
