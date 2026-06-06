<?php
// Deletes one comment only when it belongs to the currently logged-in user.
require("db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: text/plain; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo "error";
    exit;
}

$comment_id = (int)($_POST["comment_id"] ?? 0);
$user_id = (int)$_SESSION["user_id"];

if ($comment_id <= 0 || $user_id <= 0) {
    echo "error";
    exit;
}

// Confirm that this comment belongs to the current user before deleting it.
$check_stmt = $conn->prepare(
    "SELECT comment_id
     FROM comments
     WHERE comment_id = ? AND user_id = ?
     LIMIT 1"
);
if (!$check_stmt) { echo "error"; exit; }

$check_stmt->bind_param("ii", $comment_id, $user_id);
$check_stmt->execute();
$comment_exists = $check_stmt->get_result()->num_rows > 0;
$check_stmt->close();

if (!$comment_exists) {
    echo "error";
    exit;
}

$delete_stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
if (!$delete_stmt) { echo "error"; exit; }
$delete_stmt->bind_param("i", $comment_id);
$delete_ok = $delete_stmt->execute();
$delete_stmt->close();

echo $delete_ok ? "ok" : "error";
?>
