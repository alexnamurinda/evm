<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $position = $_POST['position'];
    $candidate = $_POST['candidate'];

    try {
        // Increment vote count
        $query = "UPDATE assigned_candidates SET votes = votes + 1 WHERE candidate_post = ? AND candidate_name = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$position, $candidate]);

        echo json_encode(["message" => "Vote recorded successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid Request"]);
}
?>
