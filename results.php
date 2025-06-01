<?php
require 'db_connection.php';

// Fetch total valid votes
$stmt = $conn->query("SELECT SUM(votes) as totalVotes FROM assigned_candidates");
$result = $stmt->fetch();
$totalVotes = $result['totalVotes'] ?? 0;

// Fetch all available positions
$stmt = $conn->query("SELECT DISTINCT candidate_post FROM assigned_candidates");
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize candidates array
$candidates = [];
$position = '';

// Check if a position is selected
if (isset($_GET['position']) && !empty($_GET['position'])) {
    $position = $_GET['position'];

    // Fetch candidates for the selected position, ordered by votes (highest first)
    $stmt = $conn->prepare("SELECT candidate_name, course, candidate_photo, votes 
                            FROM assigned_candidates 
                            WHERE candidate_post = :position 
                            ORDER BY votes DESC");
    $stmt->execute(['position' => $position]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h2>Election Results</h2>
<p><strong>Total Valid Votes:</strong> <?php echo number_format($totalVotes); ?></p>

<form method="GET" action="results.php">
    <label for="position">Select Position:</label>
    <select name="position" id="position" required>
        <option value="">-- Select Position --</option>
        <?php foreach ($positions as $pos): ?>
            <option value="<?php echo htmlspecialchars($pos['candidate_post']); ?>" 
                <?php echo ($position == $pos['candidate_post']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($pos['candidate_post']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">View Results</button>
</form>

<?php if (!empty($candidates)): ?>
    <h3>Results for: <?php echo htmlspecialchars($position); ?></h3>
    <table border="1">
        <tr>
            <th>Photo</th>
            <th>Candidate Name</th>
            <th>Course</th>
            <th>Votes</th>
        </tr>
        <?php foreach ($candidates as $candidate): ?>
            <tr>
                <td>
                    <img src="uploads/<?php echo !empty($candidate['candidate_photo']) ? htmlspecialchars($candidate['candidate_photo']) : 'default.jpg'; ?>" 
                         width="50" height="50" alt="Candidate Photo">
                </td>
                <td><?php echo htmlspecialchars($candidate['candidate_name']); ?></td>
                <td><?php echo htmlspecialchars($candidate['course']); ?></td>
                <td><?php echo number_format($candidate['votes']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif ($position): ?>
    <p>No candidates found for this position.</p>
<?php endif; ?>
