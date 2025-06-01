<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration</title>
</head>
<body>
  <h2>Student Registration Form</h2>
  <form action="registerStudent.php" method="post">
    <label for="student_id">Student ID:</label><br>
    <input type="text" id="student_id" name="student_id" required><br><br>
    
    <label for="student_name">Student Name:</label><br>
    <input type="text" id="student_name" name="student_name" required><br><br>
    
    <label for="course">Course:</label><br>
    <input type="text" id="course" name="course" required><br><br>
    
    <label for="program">Program:</label><br>
    <select id="program" name="program" required>
      <option value="Day">Day</option>
      <option value="Evening">Evening</option>
      <option value="Weekend">Weekend</option>
    </select><br><br>
    
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    
    <input type="submit" value="Submit">
  </form>
</body>
</html>
