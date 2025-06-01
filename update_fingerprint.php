<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required parameters are set
    if (isset($_POST['student_id']) && isset($_POST['fp_template'])) {
        $studentID = $_POST['student_id'];
        $fp_template = $_POST['fp_template'];

        try {
            // Decode the base64 template to get the raw fingerprint data
            $fpTemplateData = base64_decode($fp_template);

            // Check if decoding was successful
            if ($fpTemplateData === false) {
                echo "Error: Invalid fingerprint template data.";
                exit;
            }

            // Prepare the SQL statement to update the fingerprint template for the student
            $stmt = $conn->prepare("UPDATE students SET fp_template = :fp_template WHERE student_id = :student_id");
            $stmt->bindParam(':fp_template', $fpTemplateData, PDO::PARAM_LOB);
            $stmt->bindParam(':student_id', $studentID, PDO::PARAM_STR);
            
            // Execute the statement
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->rowCount() > 0) {
                echo "Fingerprint stored successfully!";
            } else {
                echo "Error: No matching student found or no changes made.";
            }

        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    } else {
        echo "Error: Missing required parameters (student_id, fp_template).";
    }
}
