<?php
// AJAX endpoint: lets an administrator delete ANY comment (moderation).
require("db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: text/plain; charset=UTF-8");

// admin only
if ($_SERVER["REQUEST_METHOD"] !== "POST"
    || !isset($_SESSION["user_id"])
    || empty($_SESSION["is_admin"]) || (int)$_SESSION["is_admin"] !== 1) {
    echo "error";
    exit;
}

$comment_id = (int)($_POST["comment_id"] ?? 0);
if ($comment_id <= 0) {
    echo "error";
    exit;
}

$stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
if (!$stmt) { echo "error"; exit; }
$stmt->bind_param("i", $comment_id);
$ok = $stmt->execute();
$stmt->close();

echo $ok ? "ok" : "error";
