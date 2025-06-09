<?php
// Database connection details
$db_host = 'localhost';
$db_user = 'root';
$db_password = 'Alex@mysql123';
$db_name = 'evm';

try {
    // Connect to MySQL server without specifying a database
    $pdo = new PDO("mysql:host=$db_host;", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the database exists, if not, create it
    $createDbQuery = "CREATE DATABASE IF NOT EXISTS $db_name";
    $pdo->exec($createDbQuery);

    // Connect to the newly created database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the posts table
    $createpostsTableQuery = "
    CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_position VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB;
    ";
    $pdo->exec($createpostsTableQuery);

    // Create students table with phone field
    $createstudentsTableQuery = "
CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(50) PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL UNIQUE,  
    course VARCHAR(200) NOT NULL,
    program ENUM('Day', 'Evening', 'Weekend') NOT NULL,
    password VARCHAR(255) NOT NULL,
    fp_template VARCHAR(255) UNIQUE,
    vote_status VARCHAR(50) DEFAULT 'notyet',
    vote_method VARCHAR(50) DEFAULT NULL,
    vote_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
    $pdo->exec($createstudentsTableQuery);


    // Create assigned candidates table
    $createassigned_candidatesTableQuery = "
    CREATE TABLE IF NOT EXISTS assigned_candidates (
        student_id VARCHAR(100) PRIMARY KEY,
        candidate_post VARCHAR(100) NOT NULL,
        candidate_name VARCHAR(50) NOT NULL,
        course VARCHAR(20) NOT NULL,
        candidate_photo VARCHAR(255),
        votes INT DEFAULT 0
    ) ENGINE=InnoDB;
    ";
    $pdo->exec($createassigned_candidatesTableQuery);

    // Create feedback table
    $createFeedbackTableQuery = "
    CREATE TABLE IF NOT EXISTS feedbacks (
        feedback_id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(50) NOT NULL,
        student_email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        submitted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    $pdo->exec($createFeedbackTableQuery);

    // Create results table
    $createresultsTableQuery = "
    CREATE TABLE IF NOT EXISTS results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        results VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;
    ";
    $pdo->exec($createresultsTableQuery);

    // Create login_attempts table to track successful and failed login attempts
    $createLoginAttemptsTableQuery = "
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        status ENUM('Success', 'Failed') NOT NULL,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    $pdo->exec($createLoginAttemptsTableQuery);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
