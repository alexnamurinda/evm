<?php
// get_voting_form.php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo '<p>Error: User not logged in</p>';
    exit;
}

try {
    // Get all distinct positions
    $stmt = $conn->query("SELECT DISTINCT candidate_post FROM assigned_candidates ORDER BY candidate_post");
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($positions)) {
        echo '<p>No positions available for voting at this time.</p>';
        exit;
    }

    // Start building the form
    echo '<form id="vote-form" class="voting-form">';

    // For each position, get all candidates
    foreach ($positions as $position) {
        $post = $position['candidate_post'];

        echo '<div class="position-container" data-position="' . htmlspecialchars($post) . '">';
        echo '<h4>' . htmlspecialchars($post) . '</h4>';

        // Get candidates for this position
        $stmt = $conn->prepare("SELECT student_id, candidate_name, course, candidate_photo FROM assigned_candidates WHERE candidate_post = :post");
        $stmt->execute(['post' => $post]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($candidates)) {
            echo '<p>No candidates available for this position.</p>';
        } else {
            echo '<div class="candidates-container">';

            foreach ($candidates as $candidate) {
                $candidate_id = $candidate['student_id'];
                $name = $candidate['candidate_name'];
                $course = $candidate['course'];
                $photoPath = (!empty($candidate['candidate_photo']) && file_exists('IMAGES/' . $candidate['candidate_photo']))
                    ? 'IMAGES/' . $candidate['candidate_photo']
                    : 'IMAGES/candidate.png';

                echo '<div class="candidate-card">';
                echo '<img src="' . htmlspecialchars($photo) . '" alt="' . htmlspecialchars($name) . '" class="candidate-photo">';
                echo '<div class="candidate-info">';
                echo '<p class="candidate-name">' . htmlspecialchars($name) . '</p>';
                echo '<p class="candidate-course">' . htmlspecialchars($course) . '</p>';
                echo '</div>';
                echo '<div class="candidate-select">';
                echo '<input type="radio" name="vote[' . htmlspecialchars($post) . ']" value="' . htmlspecialchars($candidate_id) . '" id="candidate-' . htmlspecialchars($candidate_id) . '">';
                echo '<label for="candidate-' . htmlspecialchars($candidate_id) . '">Vote</label>';
                echo '</div>';
                echo '</div>';
            }

            echo '</div>'; // End candidates-container
        }

        echo '</div>'; // End position-container
    }

    echo '<div class="form-actions">';
    echo '<button type="submit" class="submit-vote-btn">Submit Vote</button>';
    echo '</div>';
    echo '</form>';
} catch (PDOException $e) {
    echo '<p>Error: ' . $e->getMessage() . '</p>';
}
