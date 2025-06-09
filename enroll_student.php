<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentID = $_POST['studentID'];
    $name = $_POST['name'];
    $course = $_POST['course'];
    $program = $_POST['program'];
    $phone = $_POST['phone'];  // Capture the phone number as entered

    // Check if student already exists
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$studentID]);
    $existingStudent = $stmt->fetch();

    echo '<div style="display: flex; justify-content: center; align-items: center; height: 100vh; text-align: center; font-family: Arial, sans-serif;">';
    echo '<div style="border: 1px solid #ccc; border-radius: 10px; padding: 30px; width: 500px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">';

    if ($existingStudent) {
        echo '<h2 style="color: red;">Student ID already enrolled!</h2>';
        echo '<button onclick="window.history.back()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 8px;">RETRY</button>';
    } else {
        $esp_ip = "192.168.1.5"; // ESP32 IP address

        $response = @file_get_contents("http://$esp_ip/capture_fingerprint");

        if ($response === "enrollment_started") {
            echo '<h2 style="color: blue;">Place finger on the sensor</h2>';
            echo '<div id="status">Waiting for fingerprint...</div>';
            echo '<div id="loader" style="border: 16px solid #f3f3f3; border-top: 16px solid #3498db; border-radius: 50%; width: 80px; height: 80px; animation: spin 2s linear infinite; margin: 20px auto;"></div>';
            echo '<style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';

            echo '<script>
                const studentID = "' . $studentID . '";
                const name = "' . $name . '";
                const phone = "' . $phone . '";
                const course = "' . $course . '";
                const program = "' . $program . '";
                
                function checkEnrollmentStatus() {
                    fetch("check_status.php?ts=" + new Date().getTime())
                        .then(response => response.text())
                        .then(data => {
                            console.log("Raw ESP32 Response:", data);
                            data = data.trim();
                            
                            if (data && data !== "waiting") {
                                console.log("Processing response:", data);
                                
                                if (data === "failed") {
                                    document.getElementById("status").innerHTML = " ❌ Enrollment failed.";
                                    document.getElementById("loader").style.display = "none";
                                    showRetryButton();
                                } else {
                                    const fingerprintID = data;
                                    document.getElementById("status").innerHTML = " ✅ Enrollment successful!";
                                    document.getElementById("loader").style.display = "none";
                                    saveStudentData(fingerprintID);
                                }
                            } else {
                                setTimeout(checkEnrollmentStatus, 2000);
                            }
                        })
                        .catch(error => {
                            console.error("Error checking enrollment status:", error);
                            setTimeout(checkEnrollmentStatus, 2000);
                        });
                }
                
                function saveStudentData(fingerprintID) {
                    console.log("Saving student with fingerprint ID:", fingerprintID);
                    
                    const formData = new FormData();
                    formData.append("studentID", studentID);
                    formData.append("name", name);
                    formData.append("phone", phone);
                    formData.append("course", course);
                    formData.append("program", program);
                    formData.append("enrolledId", fingerprintID);
                    
                    fetch("save_student.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        console.log("Save result:", result);
                        if (result === "success") {
                            document.getElementById("status").innerHTML = "Student data saved successfully!";
                            showOkButton();
                        } else {
                            document.getElementById("status").innerHTML = "Error saving student data: " + result;
                            showRetryButton();
                        }
                    })
                    .catch(error => {
                        console.error("Save error:", error);
                        document.getElementById("status").innerHTML = "Error saving student data.";
                        showRetryButton();
                    });
                }
                
                function showRetryButton() {
                    const button = document.createElement("button");
                    button.innerHTML = "RETRY";
                    button.style = "padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 8px; margin-top: 15px;";
                    button.onclick = function() { window.history.back(); };
                    document.getElementById("status").after(button);
                }

                function showOkButton() {
                    const button = document.createElement("button");
                    button.innerHTML = "OK";
                    button.style = "padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 8px; margin-top: 15px;";
                    button.onclick = function() { window.location.href = "admin.php"; };
                    document.getElementById("status").after(button);
                }

                checkEnrollmentStatus();
            </script>';
        } else {
            echo '<h2 style="color: red;">Error: Could not connect to fingerprint sensor.</h2>';
            echo '<button onclick="window.history.back()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 8px;">RETRY</button>';
        }
    }

    echo '</div>';
    echo '</div>';
}
