<?php
// Toggles one favorite record for the current user and selected recipe.
require("db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: text/plain; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo "error";
    exit;
}

$recipe_id = (int)($_POST["recipe_id"] ?? 0);
$user_id = (int)$_SESSION["user_id"];

if ($recipe_id <= 0 || $user_id <= 0) {
    echo "error";
    exit;
}

// Check whether the favorite already exists for this user and recipe.
$check_stmt = $conn->prepare("SELECT id FROM favorites WHERE recipe_id = ? AND user_id = ? LIMIT 1");
if (!$check_stmt) {
    echo "error";
    exit;
}

$check_stmt->bind_param("ii", $recipe_id, $user_id);
$check_stmt->execute();
$favorite_row = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($favorite_row) {
    // Remove the favorite when the record is already present.
    $delete_stmt = $conn->prepare("DELETE FROM favorites WHERE id = ?");
    if (!$delete_stmt) { echo "error"; exit; }
    $favorite_id = (int)$favorite_row["id"];
    $delete_stmt->bind_param("i", $favorite_id);
    $delete_ok = $delete_stmt->execute();
    $delete_stmt->close();
    echo $delete_ok ? "removed" : "error";
    exit;
}

// Add the favorite when the record does not exist yet.
$insert_stmt = $conn->prepare("INSERT INTO favorites (recipe_id, user_id) VALUES (?, ?)");
if (!$insert_stmt) { echo "error"; exit; }
$insert_stmt->bind_param("ii", $recipe_id, $user_id);
$insert_ok = $insert_stmt->execute();
$insert_stmt->close();

echo $insert_ok ? "added" : "error";
?>
