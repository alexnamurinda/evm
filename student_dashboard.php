<?php
require 'db_connection.php';
session_start();
//Redirect to login if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch student details from session
$userName = $_SESSION['student_name'];
$course = $_SESSION['course'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>evm_student_portal</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <link rel="stylesheet" href="responsive.css">

    <script src="mobile-menu.js"></script>

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <?php
    require 'db_connection.php';

    // Fetch total valid votes
    $stmt = $conn->query("SELECT SUM(votes) as totalVotes FROM assigned_candidates");
    $result = $stmt->fetch();
    $totalVotes = $result['totalVotes'] ?? 0;

    // Fetch all available positions
    $stmt = $conn->query("SELECT DISTINCT candidate_post FROM assigned_candidates");
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <img src="IMAGES/logo.jpeg" alt="Logo">
            </div>
            <div class="navbar-profile">
                <img src="iMAGES/candidate.png" alt="Profile Photo" class="profile-photo">
                <div class="profile-info">
                    <p id="greeting" class="greeting"></p>
                    <p class="email"><?php echo htmlspecialchars($course); ?></p>
                </div>
            </div>

            <div class="navbar-signout">
                <a href="logout.php"><button class="signout-button">SignOut</button></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="links">
                <a href="#" id="home-link"><i class="fas fa-home"></i> Home</a>
                <a href="#" id="prediction-link"><i class="fas fa-brain"></i> Vote Online</a>
                <a href="#" id="results-link"><i class="fas fa-chart-bar"></i> Results</a>
                <a href="#" id="feedback-link"><i class="fas fa-comment-alt"></i> Send Feedback</a>
            </div>
            <div class="cal">
                <div id="calendar"></div>
            </div>
        </div>


        <!-- Main Content -->
        <div class="main-content">
            <!-- Home Section -->
            <div id="home" class="slider">
                <p class="slider_heading">ISBAT_EVM Project<br /></p>
                <p class="slider_par1">Developed by:</p>
                <p class="slider_par2">Eng. William ft Eng Alex</p>
            </div>

            <!-- Results Section -->
            <div id="results" style="display: none;">
                <p><strong>Total Valid Votes:</strong> <span id="totalVotes"><?php echo number_format($totalVotes); ?></span></p>

                <!-- Card Container for Positions and Candidates -->
                <div class="cards">
                    <?php foreach ($positions as $pos): ?>
                        <div class="card">
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

            <!-- Feedback Section -->
            <div id="feedback" style="display:none;">
                <h3 class="feedback-title">Submit Your Feedback</h3>

                <form id="feedbackForm" method="POST" class="feedback-form">
                    <div class="input-group">
                        <label for="student_email">Email:</label>
                        <input type="email" id="student_email" name="student_email" required>
                    </div>

                    <div class="input-group">
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Submit Feedback</button>
                </form>

                <p id="feedbackMessage" class="feedback-message"></p>
            </div>

            <script>
                document.getElementById("feedbackForm").addEventListener("submit", function(event) {
                    event.preventDefault();

                    var formData = new FormData(this);

                    fetch("insert_feedback.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById("feedbackMessage").innerHTML = data;
                            document.getElementById("feedbackForm").reset();
                        })
                        .catch(error => console.error("Error:", error));
                });
            </script>

            <div id="prediction" style="display: none;">
                <div id="voting-status-container"></div>
                <div id="voting-form-container"></div>
            </div>

            <script>
                // Add this to your existing event listeners
                const predictionLink = document.getElementById("prediction-link");
                const predictionSection = document.getElementById("prediction");

                predictionLink.addEventListener("click", function() {
                    homeSection.style.display = "none";
                    resultsSection.style.display = "none";
                    feedbackSection.style.display = "none";
                    predictionSection.style.display = "block";

                    // Check voting status when this section is opened
                    checkVotingStatus();
                });

                // Global variable to store the selected votes
                let userSelections = {};
                // Global variable to store current position index
                let currentPositionIndex = 0;
                // Global variable to store total positions
                let totalPositions = 0;
                // Global variable to store position data
                let positionsData = [];

                // Function to check if student has already voted
                function checkVotingStatus() {
                    fetch("check_voting_status.php")
                        .then(response => response.json())
                        .then(data => {
                            const statusContainer = document.getElementById("voting-status-container");
                            const formContainer = document.getElementById("voting-form-container");

                            if (data.status === "done") {
                                // Student has already voted
                                statusContainer.innerHTML = `
                        <div class="alert alert-info">
                            <p>You have already voted via <strong>${data.method}</strong> on ${data.time}</p>
                        </div>
                    `;
                                formContainer.innerHTML = ""; // Clear the form container
                            } else {
                                // Student has not voted yet, display voting form
                                statusContainer.innerHTML = `
                        <div class="alert alert-success">
                            <p>You can now cast your vote online.</p>
                        </div>
                    `;

                                // Load voting form
                                fetch("get_voting_positions.php")
                                    .then(response => response.json())
                                    .then(positions => {
                                        positionsData = positions;
                                        totalPositions = positions.length;

                                        if (totalPositions > 0) {
                                            // Initialize with the first position
                                            currentPositionIndex = 0;
                                            displayCurrentPosition();
                                        } else {
                                            formContainer.innerHTML = "<p>No positions available for voting at this time.</p>";
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error loading positions:", error);
                                        formContainer.innerHTML = "<p>Error loading voting positions. Please try again later.</p>";
                                    });
                            }
                        })
                        .catch(error => {
                            console.error("Error checking voting status:", error);
                            document.getElementById("voting-status-container").innerHTML =
                                "<p>Error checking voting status. Please try again later.</p>";
                        });
                }

                function displayCurrentPosition() {
                    const formContainer = document.getElementById("voting-form-container");

                    if (currentPositionIndex < 0 || currentPositionIndex >= totalPositions) {
                        return;
                    }

                    const position = positionsData[currentPositionIndex];

                    fetch(`get_position_candidates.php?position=${encodeURIComponent(position.candidate_post)}`)
                        .then(response => response.json())
                        .then(candidates => {
                            let html = `
                <div class="voting-form">
                    <div class="position-progress">
                        <span>Position ${currentPositionIndex + 1} of ${totalPositions}</span>
                    </div>
                    <div class="position-container" data-position="${position.candidate_post}">
                        <h4>${position.candidate_post}</h4>
                        <div class="candidates-container">
            `;

                            if (candidates.length === 0) {
                                html += `<p>No candidates available for this position.</p>`;
                            } else {
                                candidates.forEach(candidate => {
                                    const isChecked = userSelections[position.candidate_post] === candidate.student_id ? 'checked' : '';
                                    const photo = candidate.candidate_photo ? candidate.candidate_photo : 'IMAGES/candidate.png';

                                    html += `
                        <div class="candidate-card">
                            <img src="${photo}" alt="${candidate.candidate_name}" class="candidate-photo">
                            <div class="candidate-info">
                                <p class="candidate-name">${candidate.candidate_name}</p>
                                <p class="candidate-course">${candidate.course}</p>
                            </div>
                            <div class="candidate-select">
                                <input type="radio" name="vote[${position.candidate_post}]" 
                                    value="${candidate.student_id}" 
                                    id="candidate-${candidate.student_id}" ${isChecked} 
                                    onchange="saveSelection('${position.candidate_post}', '${candidate.student_id}')">
                                <label for="candidate-${candidate.student_id}">Vote</label>
                            </div>
                        </div>
                    `;
                                });
                            }

                            html += `
                        </div>
                    </div>
                    <div class="position-navigation">
            `;

                            if (currentPositionIndex > 0) {
                                html += `<button type="button" class="nav-btn prev-btn" onclick="navigatePosition(-1)">Previous</button>`;
                            } else {
                                html += `<button type="button" class="nav-btn prev-btn" disabled>Previous</button>`;
                            }

                            if (currentPositionIndex < totalPositions - 1) {
                                html += `<button type="button" class="nav-btn next-btn" onclick="navigatePosition(1)">Next</button>`;
                            } else {
                                html += `<button type="button" class="submit-vote-btn" onclick="submitVote()">Submit Vote</button>`;
                            }

                            html += `
                    </div>
                </div>
            `;

                            formContainer.innerHTML = html;
                        })
                        .catch(error => {
                            console.error("Error loading candidates:", error);
                            formContainer.innerHTML = "<p>Error loading candidates. Please try again later.</p>";
                        });
                }
                // Function to save the current selection
                function saveSelection(position, candidateId) {
                    userSelections[position] = candidateId;
                }

                // Function to navigate between positions
                function navigatePosition(direction) {
                    // Save current selections first
                    const currentPosition = positionsData[currentPositionIndex].candidate_post;
                    const selectedCandidate = document.querySelector(`input[name="vote[${currentPosition}]"]:checked`);

                    if (selectedCandidate) {
                        userSelections[currentPosition] = selectedCandidate.value;
                    }

                    // Update position index
                    currentPositionIndex += direction;

                    // Ensure the index is within bounds
                    if (currentPositionIndex < 0) {
                        currentPositionIndex = 0;
                    } else if (currentPositionIndex >= totalPositions) {
                        currentPositionIndex = totalPositions - 1;
                    }

                    // Display the new position
                    displayCurrentPosition();
                }

                // Function to submit the vote
                function submitVote() {
                    // Check for unvoted positions
                    let unvotedPositions = [];

                    positionsData.forEach(position => {
                        if (!userSelections[position.candidate_post]) {
                            unvotedPositions.push(position.candidate_post);
                        }
                    });

                    if (unvotedPositions.length > 0) {
                        const unvotedList = unvotedPositions.join(", ");
                        if (!confirm(`You didn't vote for the following position(s): ${unvotedList}. Do you want to continue without voting for these positions?`)) {
                            return; // User chose to go back and edit selections
                        }
                    }

                    // Create form data from selections
                    const formData = new FormData();

                    for (const position in userSelections) {
                        formData.append(`vote[${position}]`, userSelections[position]);
                    }

                    // Submit the vote
                    fetch("submit_vote.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Your vote has been successfully recorded!");
                                // Reset selections
                                userSelections = {};
                                // Refresh the voting status
                                checkVotingStatus();
                            } else {
                                alert("Error: " + data.message);
                            }
                        })
                        .catch(error => {
                            console.error("Error submitting vote:", error);
                            alert("An error occurred while submitting your vote. Please try again.");
                        });
                }
            </script>


        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="sidebar-title">
                <h4>Voting Period</h4>
                <p id="countdown" class="card-number">Loading...</p>
            </div>

            <!-- <div class="sidebar-section">
                <h4>Account Settings</h4>
                <ul>
                    <li><a href="change_password.php">🔑 Change Password</a></li>
                </ul>
            </div> -->
        </div>


        <script>
            // Countdown Timer Function
            function startCountdown(endTime) {
                const countdownElement = document.getElementById("countdown");

                // Update the countdown every 1 second
                const countdownInterval = setInterval(function() {
                    const currentTime = new Date().getTime();
                    const timeRemaining = endTime - currentTime;

                    if (timeRemaining <= 0) {
                        clearInterval(countdownInterval);
                        countdownElement.textContent = "Voting has ended!";
                    } else {
                        const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                        countdownElement.textContent = `${days}D  ${hours}h:${minutes}m:${seconds}s`;
                    }
                }, 1000);
            }

            // Set your voting period end time (change this to your desired end date and time)
            const endTime = new Date("2025-07-30T00:00:00").getTime();

            // Start the countdown
            startCountdown(endTime);
        </script>

    </div>

    <div class="footer">
        <p>Copyright 2025 - evm_project</p>
    </div>

    <!-- calender script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [{
                        title: 'Event 1',
                        start: '2023-07-01'
                    },
                    {
                        title: 'Event 2',
                        start: '2023-07-02',
                        end: '2023-07-03'
                    }
                    // Add more events here
                ],
                dayRender: function(arg) {
                    var today = new Date().toISOString().slice(0, 10);
                    if (arg.dateStr === today) {
                        arg.el.classList.add('today');
                    }
                },
                dateClick: function(info) {
                    var clickedDate = info.dateStr;
                    var today = new Date().toISOString().slice(0, 10);

                    if (clickedDate !== today) {
                        calendar.setOption('title', clickedDate);
                    }
                }
            });

            calendar.render();
        });
    </script>

    <!-- Greeting Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userName = "<?php echo htmlspecialchars(ucwords(strtolower(strtok($userName, ' ')))); ?>";
            const now = new Date();
            const hours = now.getHours();
            let greeting;

            if (hours < 12) {
                greeting = `Hello, ${userName}!`;
            } else if (hours < 18) {
                greeting = `Hello, ${userName}!`;
            } else {
                greeting = `Hello, ${userName}!`;
            }

            document.getElementById('greeting').textContent = greeting;
        });
    </script>

    <!-- sections script -->
    <script>
        // Select links and sections
        const homeLink = document.getElementById('home-link');
        const resultsLink = document.getElementById('results-link');

        const feedbackLink = document.getElementById('feedback-link');

        const homeSection = document.getElementById('home');
        const resultsSection = document.getElementById('results');
        const feedbackSection = document.getElementById('feedback');

        // Event listeners for links
        homeLink.addEventListener('click', function() {
            homeSection.style.display = 'block';
            resultsSection.style.display = 'none';
            feedbackSection.style.display = 'none';
        });

        resultsLink.addEventListener('click', function() {
            homeSection.style.display = 'none';
            resultsSection.style.display = 'block';
            feedbackSection.style.display = 'none';
        });

        feedbackLink.addEventListener('click', function() {
            homeSection.style.display = 'none';
            resultsSection.style.display = 'none';
            feedbackSection.style.display = 'block';
        });
    </script>
</body>

</html>