<?php
// Include database connection
include 'db_connection.php';

// Get POST data
$student_id = $_POST['student_id'];
$student_name = $_POST['student_name'];
$course = $_POST['course'];
$program = $_POST['program'];
$password = $_POST['password'];
$fp_template = $_POST['fp_template'];

// Prepare the query to insert data
$query = "INSERT INTO students (student_id, student_name, course, program, password, fp_template) 
          VALUES (:student_id, :student_name, :course, :program, :password, :fp_template)";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
$stmt->bindParam(':student_id', $student_id);
$stmt->bindParam(':student_name', $student_name);
$stmt->bindParam(':course', $course);
$stmt->bindParam(':program', $program);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':fp_template', $fp_template);

if ($stmt->execute()) {
    echo "Student added successfully!";
} else {
    echo "Failed to add student!";
}
