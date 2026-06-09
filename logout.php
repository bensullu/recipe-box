<?php
// Destroys the session and redirects to the login page

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data and end the session
$_SESSION = [];
session_destroy();

header("Location: index.php");
exit;
?>
