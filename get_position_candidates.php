<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$position = $_GET['position'] ?? '';
if (empty($position)) {
    echo json_encode(['error' => 'Position is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT student_id, candidate_name, course, candidate_photo FROM assigned_candidates WHERE candidate_post = :position");
    $stmt->execute(['position' => $position]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepend image paths
    foreach ($candidates as &$candidate) {
        $candidate['candidate_photo'] = !empty($candidate['candidate_photo'])
            ? 'IMAGES/' . $candidate['candidate_photo']
            : 'IMAGES/candidate.png';
    }

    echo json_encode($candidates);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
