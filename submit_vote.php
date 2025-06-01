<?php
// submit_vote.php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$student_id = $_SESSION['student_id'];

// Verify if the student has already voted
try {
    $stmt = $conn->prepare("SELECT vote_status FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student && $student['vote_status'] === 'done') {
        echo json_encode(['success' => false, 'message' => 'You have already voted']);
        exit;
    }
    
    // Process the votes
    $votes = $_POST['vote'] ?? [];
    
    if (empty($votes)) {
        echo json_encode(['success' => false, 'message' => 'No votes submitted']);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Update vote counts for selected candidates
    foreach ($votes as $position => $candidate_id) {
        $stmt = $conn->prepare("UPDATE assigned_candidates SET votes = votes + 1 WHERE student_id = :candidate_id AND candidate_post = :position");
        $stmt->execute([
            'candidate_id' => $candidate_id,
            'position' => $position
        ]);
    }
    
    // Update student's voting status
    $stmt = $conn->prepare("UPDATE students SET vote_status = 'done', vote_method = 'online', vote_time = CURRENT_TIMESTAMP WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Vote successfully recorded']);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>