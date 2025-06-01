<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include 'db_connection.php';

try {
    $query = "SELECT candidate_post, candidate_name FROM assigned_candidates";
    $stmt = $conn->query($query);

    $candidates = [];

    while ($row = $stmt->fetch()) {
        $post = $row['candidate_post'];
        $name = $row['candidate_name'];
        
        if (!isset($candidates[$post])) {
            $candidates[$post] = [];
        }
        $candidates[$post][] = $name;
    }

    // Send JSON response
    echo json_encode($candidates);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database Error: " . $e->getMessage()]);
}
?>
