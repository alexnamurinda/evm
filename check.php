<?php
// Include the shared database connection
require 'db_connection.php';

$fp_template = isset($_GET['fp_template']) ? $_GET['fp_template'] : '';

if (empty($fp_template) || !preg_match('/^F21\/\d+$/', $fp_template)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid fingerprint ID format']);
    exit;
}

try {
    // Use the shared $conn from db_connection.php
    $stmt = $conn->prepare("SELECT vote_status, vote_method FROM students WHERE fp_template = :fp_template");
    $stmt->bindParam(':fp_template', $fp_template, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'fp_template' => $fp_template,
            'vote_status' => $result['vote_status'],
            'vote_method' => $result['vote_method'],
            'student_id' => $fp_template
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Student not found',
            'action' => 'showWelcomeMessage',
            'fp_template' => $fp_template
        ]);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
