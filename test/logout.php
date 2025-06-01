
<?php
// logout.php - Logout and destroy session
session_start();
session_destroy();
header("Location: admin_login.php");
exit;
?>
