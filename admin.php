<?php
// admin.php - Admin dashboard after successful fingerprint verification
session_start();

// Verify that user is both admin AND has completed fingerprint verification
if (
    !isset($_SESSION['admin']) || $_SESSION['admin'] !== true ||
    !isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true
) {
    // Redirect unauthorized access back to login page
    header("Location: index.php");
    exit();
}

include 'db_connection.php'; 

// Continue with admin dashboard content
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVM_adminpage</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <?php
    include 'db_creation.php';

    try {
        // Total Students
        $stmt = $conn->query("SELECT COUNT(*) as totalStudents FROM students WHERE student_id!= 'admin'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalStudents = str_pad($result['totalStudents'], 2, "0", STR_PAD_LEFT); // Format as 02, 03, etc.

        // Total Feedbacks
        $stmt = $conn->query("SELECT COUNT(*) as totalFeedbacks FROM feedbacks");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalFeedbacks = $result['totalFeedbacks'];

        // Query to get the total number of votes from the assigned_candidates table
        $stmt = $conn->query("SELECT SUM(votes) as validVotes FROM assigned_candidates");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $validVotes = $result['validVotes']; // Total votes

        // Optional: If no votes are present, ensure $validVotes is set to 0
        $validVotes = $validVotes ? $validVotes : 0;

        // Fetch available posts
        $postsQuery = $conn->query("SELECT id, post_position FROM posts");
        $posts = $postsQuery->fetchAll();

        // Fetch assigned candidates if post ID is provided
        if (isset($_POST['post_id'])) {
            $postId = $_POST['post_id'];
            $stmt = $conn->prepare("SELECT * FROM assigned_candidates WHERE candidate_post = ?");
            $stmt->execute([$postId]);
            $candidates = $stmt->fetchAll();

            if (count($candidates) > 0) {
                echo "<ol class='assigned-candidates-list'>";
                foreach ($candidates as $candidate) {
                    echo "<li>
                    <div class='candidate-item'>
                        <img src='IMAGES/{$candidate['candidate_photo']}' class='candidate-photo' alt='photo'>
                        <span class='candidate-info'>{$candidate['candidate_name']} ({$candidate['course']})</span>
                    </div>
                  </li>";
                }
                echo "</ol>";
            } else {
                echo "<p class='no-candidates'>No candidates assigned yet.</p>";
            }
            exit;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Fetch total valid votes
    $stmt = $conn->query("SELECT SUM(votes) as totalVotes FROM assigned_candidates");
    $result = $stmt->fetch();
    $totalVotes = $result['totalVotes'] ?? 0;

    // Fetch all available positions
    $stmt = $conn->query("SELECT DISTINCT candidate_post FROM assigned_candidates");
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>


    <style>
        /* Styled Table for Login Attempts */
        .styled-table {
            width: 97%;
            border-collapse: collapse;
            margin: 15px;
            font-size: 0.9em;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            border-radius: 0px;
            overflow: hidden;
        }

        .styled-table thead tr {
            background-color: #383976;
            color: #ffffff;
            text-align: left;
        }

        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #383976;
        }

        .styled-table tbody tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        /* Status colors */
        .styled-table td:nth-child(3) {
            font-weight: bold;
        }

        .styled-table td:nth-child(3):contains('success') {
            color: #28a745;
        }

        .styled-table td:nth-child(3):contains('failed') {
            color: #dc3545;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .styled-table {
                display: block;
                overflow-x: auto;
            }

            .styled-table thead,
            .styled-table tbody,
            .styled-table th,
            .styled-table td,
            .styled-table tr {
                display: block;
            }

            .styled-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            .styled-table tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
            }

            .styled-table td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }

            .styled-table td:before {
                position: absolute;
                top: 12px;
                left: 12px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                text-align: left;
            }

            .styled-table td:nth-of-type(1):before {
                content: "Student ID";
            }

            .styled-table td:nth-of-type(2):before {
                content: "IP Address";
            }

            .styled-table td:nth-of-type(3):before {
                content: "Status";
            }

            .styled-table td:nth-of-type(4):before {
                content: "Attempt Time";
            }
        }
    </style>

</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="IMAGES/log.png" alt="University logo">
        </div>
        <nav class="sidebar-links">
            <a href="#" onclick="toggleSection('overview')"><i class="fas fa-home"></i> Overview</a>
            <a href="#" onclick="toggleSection('student_management')"><i class="fas fa-user-graduate"></i> Enrolled Students</a>
            <a href="#" onclick="toggleSection('enroll_new_student')"><i class="fas fa-pen"></i> Enroll New Student</a>
            <a href="#" onclick="toggleSection('assign_candidate')"><i class="fas fa-users"></i> Assign Candidate</a>
            <a href="#" onclick="toggleSection('results')"><i class="fas fa-chart-bar"></i> Voting Results</a>
            <a href="#" onclick="toggleSection('feedback_management')"><i class="fas fa-comment-dots"></i> Feedbacks</a>
            <a href="#" onclick="toggleSection('login_attempts')"><i class="fas fa-user-lock"></i> Login Attempts</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>


    <!-- Main Content -->
    <div class="main-content">

        <header class="admin-header">
            <h1>Admin Dashboard - EVM</h1>
            <p>Optimized Administration for Reliable Electronic Voting and User candidate security.</p>
        </header>

        <!-- Overview Section -->
        <section id="overview" class="dashboard-section">
            <div class="cards">
                <div class="card">
                    <h3>Total Students</h3>
                    <p class="card-number"><?php echo htmlspecialchars($totalStudents); ?></p>
                </div>
                <div class="card">
                    <h3>Valid Votes</h3>
                    <p class="card-number"><?php echo htmlspecialchars($validVotes); ?></p>
                </div>
                <div class="card">
                    <h3>Feedbacks</h3>
                    <p class="card-number"><?php echo htmlspecialchars($totalFeedbacks); ?></p>
                </div>
                <div class="card">
                    <h3>Voting Period</h3>
                    <p id="countdown" class="card-number"></p>
                </div>
            </div>
        </section>



        <!-- enroll new student -->
        <div id="enroll_new_student" class="dashboard-section" style="display: none;">
            <h2>Enroll New Student</h2>
            <form id="enrollForm" action="enroll_student.php" method="POST">
                <div class="form-container">
                    <!-- Left Column: Student ID, Name -->
                    <div class="form-column">
                        <label for="studentID">Student ID:</label>
                        <input type="text" id="studentID" name="studentID" placeholder="same as roll number" required>

                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required placeholder="Enter full name">

                        <label for="phone">Phone Number:</label>
                        <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>
                    </div>

                    <!-- Right Column: Course, Program -->
                    <div class="form-column">
                        <label for="course">Course:</label>
                        <input type="text" id="course" name="course" required>

                        <label for="program">Program:</label>
                        <input type="text" id="program" name="program" placeholder="e.g. day, evening, weekend" required>
                    </div>
                </div>

                <button type="submit">Enroll</button>
            </form>
        </div>


        <!-- Feedbacks section -->
        <section id="feedback_management" class="dashboard-section" style="display: none;">
            <h2 style="margin-bottom: 1rem; text-align: center;">Feedback Messages from Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch emails from the feedbacks table
                    try {
                        $stmt = $conn->query("SELECT student_name, student_email, message FROM feedbacks");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['student_email']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['message']) . '</td>';
                            echo '</tr>';
                        }
                    } catch (PDOException $e) {
                        echo "Error fetching messages: " . $e->getMessage();
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Results Section -->
        <div id="results" class="dashboard-section" style="display: none;">
            <!-- <p><strong>Total Valid Votes:</strong> <span id="totalVotes"><?php echo number_format($totalVotes); ?></span></p> -->

            <!-- Card Container for Positions and Candidates -->
            <div class="cardz">
                <?php foreach ($positions as $pos): ?>
                    <div class="cardd">
                        <h3><?php echo htmlspecialchars($pos['candidate_post']); ?></h3>

                        <!-- Displaying Candidates for the current position -->
                        <div class="candidates">
                            <?php
                            $stmt = $conn->prepare("SELECT candidate_name, candidate_photo, votes FROM assigned_candidates WHERE candidate_post = :position ORDER BY votes DESC");
                            $stmt->execute(['position' => $pos['candidate_post']]);
                            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (!empty($candidates)): ?>
                                <table style="width: 100%; border-collapse: separate; margin-top: 5px; border: 1px solid #ddd;">
                                    <thead>
                                        <tr style="color: blue; text-align: center; font-size: 18px; border: 1px solid #ddd;">
                                            <th style="border: 1px solid #ddd; padding: 8px;">Ranking</th>
                                            <th style="border: 1px solid #ddd; padding: 8px;">Name</th>
                                            <th style="border: 1px solid #ddd; padding: 8px;">Votes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $rank = 1;
                                        foreach ($candidates as $candidate): ?>
                                            <tr>
                                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo $rank++; ?></td>
                                                <td style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 16px;"><?php echo htmlspecialchars($candidate['candidate_name']); ?></td>
                                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: #cb0718; font-size: 20px;"><?php echo number_format($candidate['votes']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No candidates found for this position.</p>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Student Management Section -->
        <section id="student_management" class="dashboard-section" style="display: none;">
            <h2 style="margin-bottom: 1rem; text-align:center;">Student Management</h2>

            <!-- Search Input -->
            <div style="position: relative; text-align:center; margin-bottom: 15px;">
                <input type="text" id="search" placeholder="Search by Student ID, Name, Course, or Program..." class="search-user"
                    style="padding-left: 15px; width: 480px; height: 35px; border-radius: 5px; border: 1px solid #ccc;">
                <i class="fas fa-search" style="position: absolute; left: 750px; top: 50%; transform: translateY(-50%);"></i>
            </div>

            <!-- Student Table -->
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="student-table">
                    <?php
                    try {
                        $stmt = $conn->query("SELECT student_id, student_name, course, program FROM students WHERE student_id!= 'admin'");

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['student_id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['course']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['program']) . '</td>';
                            echo '<td>Active</td>';
                            echo '<td>';
                            echo '<button class="action-btn action-delete" data-id="' . $row['student_id'] . '">Remove</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="6">Error fetching students: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Assign Candidate Section -->
        <section id="assign_candidate" class="dashboard-section" style="display: none;">
            <form action="assign_candidate.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="selectedPost">Select Post:</label>
                    <select name="selectedPost" id="selectedPost" onchange="clearFields()" required>
                        <option value=""></option>
                        <?php foreach ($posts as $post) { ?>
                            <option value="<?= htmlspecialchars($post['post_position']) ?>"><?= htmlspecialchars($post['post_position']) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div id="candidateContainer">
                    <div id="assignedCandidates"></div>
                    <div id="candidateList"></div>
                </div>

                <div class="button-group">
                    <button type="button" onclick="addCandidateField()" class="left-button">Add Candidate</button>
                    <input type="submit" name="assignCandidates" value="Finish" class="right-button">
                </div>
            </form>

        </section>

        <!-- login attempts -->
        <section id="login_attempts" class="dashboard-section" style="display: none;">
            <!-- <h2>Login Attempts</h2> -->
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Attempt Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch login attempts from the database
                    try {
                        $stmt = $conn->prepare("SELECT student_id, ip_address, status, attempt_time FROM login_attempts ORDER BY attempt_time DESC");
                        $stmt->execute();
                        $loginAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($loginAttempts as $attempt) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($attempt['student_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($attempt['ip_address']) . "</td>";
                            echo "<td>" . htmlspecialchars($attempt['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($attempt['attempt_time']) . "</td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='4'>Error fetching login attempts: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>

    <script src="admin.js"></script>
</body>

</html>