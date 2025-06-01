<?php
// get_voting_positions.php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    // Get all distinct positions
    $stmt = $conn->query("SELECT DISTINCT candidate_post FROM assigned_candidates ORDER BY candidate_post");
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($positions);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}