<?php
// Starts (or resumes) the session and ensures the user is logged in

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect anonymous visitors to the login page
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
?>
