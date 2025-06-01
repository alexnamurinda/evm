<?php
// Include the database connection
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assignCandidates'])) {
    $postId = $_POST['selectedPost'];
    $studentIds = $_POST['student_id'];
    $candidateNames = $_POST['candidate_name'];
    $courses = $_POST['course'];
    $candidatePhotos = $_FILES['candidate_photo'];

    try {
        $stmt = $conn->prepare("INSERT INTO assigned_candidates (student_id, candidate_post, candidate_name, course, candidate_photo) VALUES (?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($studentIds); $i++) {
            $photoName = basename($candidatePhotos['name'][$i]);
            move_uploaded_file($candidatePhotos['tmp_name'][$i], "IMAGES/" . $photoName);

            $stmt->execute([
                $studentIds[$i],
                $postId,
                $candidateNames[$i],
                $courses[$i],
                $photoName
            ]);
        }

        echo "Candidates assigned successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
