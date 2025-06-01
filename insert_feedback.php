<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['student_name'])) {
        echo "User is not logged in.";
        exit;
    }

    $student_name = $_SESSION['student_name'];
    $student_email = filter_var($_POST['student_email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message']);

    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO feedbacks (student_name, student_email, message) VALUES (:student_name, :student_email, :message)");
        $stmt->execute([
            ':student_name' => $student_name,
            ':student_email' => $student_email,
            ':message' => $message
        ]);
        echo "Feedback submitted successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
