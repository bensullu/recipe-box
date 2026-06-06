<?php
// Admin authorization guard - include on pages restricted to administrators

// Reuse the session check (also redirects anonymous users)
require("session.php");

// Block non-admin users from accessing admin-only pages
if (empty($_SESSION["is_admin"]) || (int)$_SESSION["is_admin"] !== 1) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access denied</title>
        <link rel="stylesheet" href="styles/style.css">
    </head>
    <body>
        <div class="container">
            <div class="message error">
                <h2>Access denied</h2>
                <p>You do not have permission to view this page. Administrator privileges are required.</p>
                <p><a href="index.php">Back to recipes</a></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
