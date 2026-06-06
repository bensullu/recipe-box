<?php
// Deletes a recipe by id - admin only
require("admin_guard.php");
require("db.php");

$recipe_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($recipe_id <= 0) {
    header("Location: index.php");
    exit;
}

// Remove the photo file from disk if one is associated with the recipe
$stmt = $conn->prepare("SELECT image FROM recipes WHERE recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row && !empty($row["image"]) && file_exists("images/" . $row["image"])) {
    unlink("images/" . $row["image"]);
}

// Delete recipe (cascading FK removes related comments and favorites)
$stmt = $conn->prepare("DELETE FROM recipes WHERE recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$stmt->close();

header("Location: index.php");
exit;
?>
