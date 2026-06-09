<?php
// Starts (or resumes) the session WITHOUT forcing the visitor to log in.
// Used by public pages (recipe list, recipe details, search) so guests can browse.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
