<?php
// maintain_session.php - Ensures the session is active before redirect
session_start();

// Simply confirm the session is still active
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    echo "session_active";
} else {
    // If session lost, report it
    echo "session_lost";
}
?>