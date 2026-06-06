<?php
// Database connection for the Recipe Box application

$host = "localhost";
$user = "root";
$password = "";
$database = "recipe_box";

// Open MySQLi connection using utf8mb4 for full Unicode support
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    // Do not leak the raw mysqli error message to the client
    error_log("DB connection failed: " . $conn->connect_error);
    http_response_code(500);
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

// Shared HTML escape helper, used everywhere a variable is printed in HTML context
if (!function_exists("h")) {
    function h($text) {
        return htmlspecialchars((string)($text ?? ""), ENT_QUOTES, "UTF-8");
    }
}
