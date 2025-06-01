<?php
// save_student.php - This script saves student data to the database and sends an SMS
require 'db_connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log the request for debugging
error_log("save_student.php called with POST data: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the posted data
    $studentID = $_POST['studentID'] ?? '';
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $course = $_POST['course'] ?? '';
    $program = $_POST['program'] ?? '';
    $fp_template = $_POST['enrolledId'] ?? '';

    // Validate required fields
    if (empty($studentID) || empty($name) || empty($phone) || empty($course) || empty($program) || empty($fp_template)) {
        error_log("Missing required fields: " . print_r($_POST, true));
        http_response_code(400);
        echo "error: Missing required fields";
        exit;
    }

    // Normalize phone number to +2567XXXXXXXX format
    $phone = preg_replace('/\D/', '', $phone); // Remove non-numeric characters

    if (!preg_match('/^\+2567\d{8}$/', $phone)) { // If not already in correct format
        if (preg_match('/^0[7-9]\d{8}$/', $phone)) {
            $phone = "+256" . substr($phone, 1); // Convert 07XXXXXXXX to +2567XXXXXXXX
        } else {
            error_log("Invalid phone number format: $phone");
            http_response_code(400);
            echo "error: Invalid phone number format";
            exit;
        }
    }

    try {
        // Use student ID as the default password (not hashed for SMS)
        $defaultPassword = $studentID;
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        // Log the data we're about to insert
        error_log("Inserting student data: ID=$studentID, Name=$name, Phone=$phone, Course=$course, Program=$program, FP=$fp_template");

        // Check if the phone number is already in use
        $stmt = $conn->prepare("SELECT student_id FROM students WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            error_log("Phone number already registered: $phone");
            http_response_code(400);
            echo "error: Phone number already registered";
            exit;
        }

        // Insert student details into the database
        $stmt = $conn->prepare("INSERT INTO students (student_id, student_name, phone, course, program, password, fp_template) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$studentID, $name, $phone, $course, $program, $hashedPassword, $fp_template]);

        if ($result) {
            error_log("Student added successfully: $studentID");

            // Send SMS notification
            $smsMessage = "Welcome to SmartVote. Your Student ID is: $studentID and your default password is: $defaultPassword";
            sendSms($phone, $smsMessage);

            echo "success";
        } else {
            error_log("Database error: " . print_r($stmt->errorInfo(), true));
            http_response_code(500);
            echo "error: Database error";
        }
    } catch (PDOException $e) {
        // Log and return error message
        error_log("PDO Exception: " . $e->getMessage());
        http_response_code(500);
        echo "error: " . $e->getMessage();
    }
} else {
    // Method not allowed
    http_response_code(405);
    echo "Method not allowed";
}

// Function to send SMS using Africa's Talking API
function sendSms($phone, $message)
{
    $apiUsername = 'agritech_info';
    $apiKey = 'atsk_d30afdc12c16b290766e27594e298b4c82fa0ca3d87f723f7a2576aa9a6d0b9d096fa012';
    $apiUrl = 'https://api.africastalking.com/version1/messaging';

    $postData = [
        'username' => $apiUsername,
        'to' => $phone,
        'message' => $message
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    error_log("SMS sent to $phone: Response: $response");
}
