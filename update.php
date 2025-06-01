<?php
// update.php - Endpoint to update vote status after voting

// Include the database connection file
require 'db_connection.php';

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['fp_template']) || !isset($data['vote_status']) || !isset($data['vote_method'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$full_fp_id = $data['fp_template'];
$vote_status = $data['vote_status'];
$vote_method = $data['vote_method'];

try {
    // Update vote status and method using the included $conn
    $stmt = $conn->prepare("UPDATE students SET vote_status = :vote_status, vote_method = :vote_method WHERE fp_template = :full_fp_id");
    $stmt->bindParam(':vote_status', $vote_status);
    $stmt->bindParam(':vote_method', $vote_method);
    $stmt->bindParam(':full_fp_id', $full_fp_id);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'fp_template' => $full_fp_id,
            'vote_status' => $vote_status,
            'vote_method' => $vote_method
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Student not found or no update necessary']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
