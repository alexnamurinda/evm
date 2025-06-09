// Function to toggle the visibility of sections
function toggleSection(sectionId) {
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }
}


//  JavaScript for Search Filter
document.getElementById('search').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#student-table tr');

    rows.forEach(row => {
        let studentID = row.cells[0].textContent.toLowerCase();
        let name = row.cells[1].textContent.toLowerCase();
        let course = row.cells[2].textContent.toLowerCase();
        let program = row.cells[3].textContent.toLowerCase();

        if (studentID.includes(filter) || name.includes(filter) || course.includes(filter) || program.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});



// Countdown Timer Function
function startCountdown(endTime) {
    const countdownElement = document.getElementById("countdown");

    // Update the countdown every 1 second
    const countdownInterval = setInterval(function () {
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



// Function to remove student
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.action-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const studentId = this.getAttribute('data-id');
            const confirmation = confirm('Are you sure you want to remove this user?');
            if (confirmation) {
                // Proceed with deletion
                fetch(`delete_user.php?id=${studentId}`, {
                    method: 'GET',
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('User removed successfully!');
                            // Remove the row from the table
                            this.closest('tr').remove();
                        } else {
                            alert('Error removing user: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    });
            }
        });
    });
});


//Function to display assigned candidates
function clearFields() {
    document.getElementById("candidateList").innerHTML = "";
    document.getElementById("assignedCandidates").innerHTML = "";
    loadAssignedCandidates();
}

function addCandidateField() {
    let candidateList = document.getElementById("candidateList");
    let div = document.createElement("div");
    div.innerHTML = `
        <input type="text" name="student_id[]" placeholder="Student ID or roll number" required>
        <input type="text" name="candidate_name[]" placeholder="Candidate Name" required>
        <input type="text" name="course[]" placeholder="Course" required>
        <input type="file" name="candidate_photo[]">
    `;
    candidateList.appendChild(div);
}

function loadAssignedCandidates() {
    let postId = document.getElementById("selectedPost").value;
    let assignedCandidatesDiv = document.getElementById("assignedCandidates");

    if (postId === "") {
        assignedCandidatesDiv.innerHTML = "";
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "admin.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            if (xhr.responseText.trim() !== "") {
                assignedCandidatesDiv.innerHTML = `<h3>Already Registered Candidates</h3>` + xhr.responseText;
            } else {
                assignedCandidatesDiv.innerHTML = "";
            }
        }
    };
    xhr.send("post_id=" + postId);
}
