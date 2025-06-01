<?php
require 'db_connection.php';  // This makes sure $conn is available

if (isset($_GET['position'])) {
    $postId = $_GET['position'];

    try {
        $stmt = $conn->prepare("SELECT student_id, candidate_name, course, candidate_photo FROM assigned_candidates WHERE candidate_post = ?");
        $stmt->execute([$postId]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepend image path
        foreach ($candidates as &$candidate) {
            $candidate['candidate_photo'] = !empty($candidate['candidate_photo']) 
                ? 'IMAGES/' . $candidate['candidate_photo'] 
                : 'IMAGES/candidate.png';
        }

        echo json_encode($candidates);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
