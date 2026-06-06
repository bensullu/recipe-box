<?php
// Saves a new recipe to the database - admin only
require("admin_guard.php");
require("db.php");
require("csrf.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: insert_recipe.php");
    exit;
}

$errors = [];

// CSRF check first - if the token is wrong we never even touch the DB
if (!csrf_verify("recipe_add", $_POST["csrf_token"] ?? null)) {
    $errors["_csrf"] = "Security token expired. Please reload the form and try again.";
}

$title = trim($_POST["title"] ?? "");
$category_id = (int)($_POST["category_id"] ?? 0);
$prep_raw = trim($_POST["prep_time"] ?? "");
$servings_raw = trim($_POST["servings"] ?? "");
$difficulty = trim($_POST["difficulty"] ?? "");
$ingredients = trim($_POST["ingredients"] ?? "");
$instructions = trim($_POST["instructions"] ?? "");
$image_filename = null;

$allowed_difficulty = ["Easy", "Medium", "Hard"];

if ($title === "") {
    $errors["title"] = "Title is required.";
} elseif (strlen($title) > 150) {
    $errors["title"] = "Title must be at most 150 characters.";
}

// Validate the category actually exists, not just non-empty
if ($category_id <= 0) {
    $errors["category_id"] = "Please pick a category.";
} else {
    $check_cat = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $check_cat->bind_param("i", $category_id);
    $check_cat->execute();
    if ($check_cat->get_result()->num_rows === 0) {
        $errors["category_id"] = "Selected category does not exist.";
    }
    $check_cat->close();
}

if ($prep_raw === "" || !ctype_digit($prep_raw) || (int)$prep_raw < 1 || (int)$prep_raw > 1440) {
    $errors["prep_time"] = "Preparation time must be between 1 and 1440 minutes.";
}

if ($servings_raw === "" || !ctype_digit($servings_raw) || (int)$servings_raw < 1 || (int)$servings_raw > 100) {
    $errors["servings"] = "Servings must be between 1 and 100.";
}

if (!in_array($difficulty, $allowed_difficulty, true)) {
    $errors["difficulty"] = "Invalid difficulty.";
}

if ($ingredients === "") {
    $errors["ingredients"] = "Ingredients are required.";
}

if ($instructions === "") {
    $errors["instructions"] = "Instructions are required.";
}

// Handle optional photo upload (only when fields are valid so far)
if (empty($errors) && isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $allowed_types = ["image/jpeg", "image/png", "image/webp", "image/gif"];
    $file_type = mime_content_type($_FILES["image"]["tmp_name"]);

    if (!in_array($file_type, $allowed_types, true)) {
        $errors["image"] = "Unsupported image type. Use JPG, PNG, WebP or GIF.";
    } else {
        if (!is_dir("images")) {
            mkdir("images", 0777, true);
        }
        $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_filename = uniqid("recipe_", true) . "." . strtolower($extension);
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], "images/" . $image_filename)) {
            $errors["image"] = "Failed to save the photo.";
            $image_filename = null;
        }
    }
}

// On any validation failure stash errors + old values and bounce back to the form
if (!empty($errors)) {
    $_SESSION["form_errors"]["recipe_add"] = $errors;
    $_SESSION["form_old"]["recipe_add"] = [
        "title" => $title,
        "category_id" => $category_id,
        "prep_time" => $prep_raw,
        "servings" => $servings_raw,
        "difficulty" => $difficulty,
        "ingredients" => $ingredients,
        "instructions" => $instructions,
    ];
    header("Location: insert_recipe.php");
    exit;
}

$prep_time = (int)$prep_raw;
$servings = (int)$servings_raw;

$stmt = $conn->prepare(
    "INSERT INTO recipes (title, category_id, prep_time, servings, difficulty, ingredients, instructions, image)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("siiissss", $title, $category_id, $prep_time, $servings, $difficulty, $ingredients, $instructions, $image_filename);

if ($stmt->execute()) {
    $stmt->close();
    csrf_consume("recipe_add");
    unset($_SESSION["form_errors"]["recipe_add"], $_SESSION["form_old"]["recipe_add"]);
    header("Location: index.php");
    exit;
}

error_log("Recipe insert failed: " . $conn->error);
$stmt->close();
$_SESSION["form_errors"]["recipe_add"] = ["_db" => "Failed to save recipe. Please try again."];
header("Location: insert_recipe.php");
exit;
