<?php
// Saves a comment - the author's username is taken from the session, never from the form
require("session.php");
require("db.php");
require("csrf.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$recipe_id = (int)($_POST["recipe_id"] ?? 0);
$errors = [];

if (!csrf_verify("comment_add", $_POST["csrf_token"] ?? null)) {
    $errors["_csrf"] = "Security token expired. Please reload the page and try again.";
}

$rating_raw = trim($_POST["rating"] ?? "");
$content = trim($_POST["content"] ?? "");

if ($rating_raw === "" || !ctype_digit($rating_raw)) {
    $errors["rating"] = "Rating must be a number.";
} else {
    $rating = (int)$rating_raw;
    if ($rating < 1 || $rating > 5) {
        $errors["rating"] = "Rating must be between 1 and 5.";
    }
}

if ($content === "") {
    $errors["content"] = "Comment content is required.";
} elseif (strlen($content) < 5) {
    $errors["content"] = "Comment must be at least 5 characters.";
} elseif (strlen($content) > 2000) {
    $errors["content"] = "Comment must be at most 2000 characters.";
}

// Confirm the recipe exists - guards against a forged recipe_id
if ($recipe_id <= 0) {
    $errors["_db"] = "Invalid recipe.";
} else {
    $recipe_check = $conn->prepare("SELECT recipe_id FROM recipes WHERE recipe_id = ?");
    $recipe_check->bind_param("i", $recipe_id);
    $recipe_check->execute();
    if ($recipe_check->get_result()->num_rows === 0) {
        $errors["_db"] = "Recipe not found.";
    }
    $recipe_check->close();
}

if (!empty($errors)) {
    $_SESSION["form_errors"]["comment_add"] = $errors;
    $_SESSION["form_old"]["comment_add"] = ["rating" => $rating_raw, "content" => $content];
    header("Location: details.php?id=" . $recipe_id);
    exit;
}

// Username is forced from the session to prevent impersonation
$username = $_SESSION["login"];
$rating = (int)$rating_raw;

$stmt = $conn->prepare(
    "INSERT INTO comments (recipe_id, username, rating, content) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isis", $recipe_id, $username, $rating, $content);

if ($stmt->execute()) {
    $stmt->close();
    csrf_consume("comment_add");
    header("Location: details.php?id=" . $recipe_id);
    exit;
}

error_log("Comment insert failed: " . $conn->error);
$stmt->close();
$_SESSION["form_errors"]["comment_add"] = ["_db" => "Failed to save comment. Please try again."];
header("Location: details.php?id=" . $recipe_id);
exit;
