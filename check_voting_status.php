<?php
// check_voting_status.php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$student_id = $_SESSION['student_id'];

try {
    // Prepare statement to get student's voting status
    $stmt = $conn->prepare("SELECT vote_status, vote_method, vote_time FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // Format the time for display
        $formatted_time = "";
        if (!empty($student['vote_time'])) {
            $time = strtotime($student['vote_time']);
            $formatted_time = date('F j, Y, g:i a', $time);
        }
        
        echo json_encode([
            'status' => $student['vote_status'] ?? 'notyet',
            'method' => $student['vote_method'] ?? '',
            'time' => $formatted_time
        ]);
    } else {
        echo json_encode(['status' => 'notyet', 'method' => '', 'time' => '']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>