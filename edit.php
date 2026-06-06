<?php
// Edit a recipe - admin only
require("admin_guard.php");
require("db.php");
require("csrf.php");

$recipe_id = isset($_GET["id"]) ? (int)$_GET["id"] : (int)($_POST["id"] ?? 0);

if ($recipe_id <= 0) {
    header("Location: index.php");
    exit;
}

$errors = [];
$old = null;
$allowed_difficulty = ["Easy", "Medium", "Hard"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_verify("recipe_edit_" . $recipe_id, $_POST["csrf_token"] ?? null)) {
        $errors["_csrf"] = "Security token expired. Please reload the form and try again.";
    }

    $title = trim($_POST["title"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);
    $prep_raw = trim($_POST["prep_time"] ?? "");
    $servings_raw = trim($_POST["servings"] ?? "");
    $difficulty = trim($_POST["difficulty"] ?? "");
    $ingredients = trim($_POST["ingredients"] ?? "");
    $instructions = trim($_POST["instructions"] ?? "");

    if ($title === "") { $errors["title"] = "Title is required."; }
    elseif (strlen($title) > 150) { $errors["title"] = "Title must be at most 150 characters."; }

    if ($category_id <= 0) {
        $errors["category_id"] = "Please pick a category.";
    } else {
        $cat_check = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $cat_check->bind_param("i", $category_id);
        $cat_check->execute();
        if ($cat_check->get_result()->num_rows === 0) { $errors["category_id"] = "Selected category does not exist."; }
        $cat_check->close();
    }

    if ($prep_raw === "" || !ctype_digit($prep_raw) || (int)$prep_raw < 1 || (int)$prep_raw > 1440) {
        $errors["prep_time"] = "Preparation time must be between 1 and 1440 minutes.";
    }
    if ($servings_raw === "" || !ctype_digit($servings_raw) || (int)$servings_raw < 1 || (int)$servings_raw > 100) {
        $errors["servings"] = "Servings must be between 1 and 100.";
    }
    if (!in_array($difficulty, $allowed_difficulty, true)) { $errors["difficulty"] = "Invalid difficulty."; }
    if ($ingredients === "") { $errors["ingredients"] = "Ingredients are required."; }
    if ($instructions === "") { $errors["instructions"] = "Instructions are required."; }

    // Optional: handle a new photo upload (replaces the existing one)
    $new_image = null;
    if (empty($errors) && isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $allowed_types = ["image/jpeg", "image/png", "image/webp", "image/gif"];
        $file_type = mime_content_type($_FILES["image"]["tmp_name"]);
        if (!in_array($file_type, $allowed_types, true)) {
            $errors["image"] = "Unsupported image type. Use JPG, PNG, WebP or GIF.";
        } else {
            if (!is_dir("images")) { mkdir("images", 0777, true); }
            $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $new_image = uniqid("recipe_", true) . "." . strtolower($extension);
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], "images/" . $new_image)) {
                $errors["image"] = "Failed to save the photo.";
                $new_image = null;
            }
        }
    }

    if (empty($errors)) {
        $prep_time = (int)$prep_raw;
        $servings = (int)$servings_raw;

        // Remember the old image so we can delete it after a successful swap
        $old_image = null;
        if ($new_image !== null) {
            $old_img_stmt = $conn->prepare("SELECT image FROM recipes WHERE recipe_id = ?");
            $old_img_stmt->bind_param("i", $recipe_id);
            $old_img_stmt->execute();
            $old_image = ($old_img_stmt->get_result()->fetch_assoc())["image"] ?? null;
            $old_img_stmt->close();

            $stmt = $conn->prepare(
                "UPDATE recipes SET title = ?, category_id = ?, prep_time = ?, servings = ?, difficulty = ?, ingredients = ?, instructions = ?, image = ? WHERE recipe_id = ?"
            );
            $stmt->bind_param("siiissssi", $title, $category_id, $prep_time, $servings, $difficulty, $ingredients, $instructions, $new_image, $recipe_id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE recipes SET title = ?, category_id = ?, prep_time = ?, servings = ?, difficulty = ?, ingredients = ?, instructions = ? WHERE recipe_id = ?"
            );
            $stmt->bind_param("siiisssi", $title, $category_id, $prep_time, $servings, $difficulty, $ingredients, $instructions, $recipe_id);
        }

        if ($stmt->execute()) {
            $stmt->close();
            // Delete the previous photo now that the new one is saved
            if ($new_image !== null && !empty($old_image) && file_exists("images/" . $old_image)) {
                unlink("images/" . $old_image);
            }
            csrf_consume("recipe_edit_" . $recipe_id);
            header("Location: details.php?id=" . $recipe_id);
            exit;
        }
        error_log("Recipe update failed: " . $conn->error);
        $stmt->close();
        // Clean up the freshly uploaded file if the DB update failed
        if ($new_image !== null && file_exists("images/" . $new_image)) {
            unlink("images/" . $new_image);
        }
        $errors["_db"] = "Failed to save changes. Please try again.";
    }

    // Keep submitted values so the form re-renders with the user's input
    $old = [
        "title" => $title,
        "category_id" => $category_id,
        "prep_time" => $prep_raw,
        "servings" => $servings_raw,
        "difficulty" => $difficulty,
        "ingredients" => $ingredients,
        "instructions" => $instructions,
    ];
}

// Load the current recipe record (first load and after validation)
$stmt = $conn->prepare("SELECT recipe_id, title, category_id, prep_time, servings, difficulty, ingredients, instructions, image FROM recipes WHERE recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recipe) {
    header("Location: index.php");
    exit;
}

$categories_result = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC");

$values = $old ?? [
    "title" => $recipe["title"],
    "category_id" => $recipe["category_id"],
    "prep_time" => $recipe["prep_time"],
    "servings" => $recipe["servings"],
    "difficulty" => $recipe["difficulty"],
    "ingredients" => $recipe["ingredients"],
    "instructions" => $recipe["instructions"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit recipe - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <h1>Edit recipe</h1>

        <?php if (!empty($errors["_csrf"])): ?>
            <div class="message error"><?= h($errors["_csrf"]) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors["_db"])): ?>
            <div class="message error"><?= h($errors["_db"]) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php" enctype="multipart/form-data" class="recipe-form" id="recipe-edit-form" novalidate>
            <input type="hidden" name="id" value="<?= (int)$recipe["recipe_id"] ?>">
            <?php csrf_field("recipe_edit_" . $recipe_id); ?>

            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title"
                       value="<?= h($values["title"]) ?>"
                       required minlength="1" maxlength="150">
                <?php if (!empty($errors["title"])): ?><span class="error-message"><?= h($errors["title"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php while ($c = $categories_result->fetch_assoc()): ?>
                        <option value="<?= (int)$c["category_id"] ?>" <?= ((int)$c["category_id"] === (int)$values["category_id"]) ? "selected" : "" ?>>
                            <?= h($c["name"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if (!empty($errors["category_id"])): ?><span class="error-message"><?= h($errors["category_id"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="prep_time">Preparation time (minutes)</label>
                <input type="number" id="prep_time" name="prep_time"
                       value="<?= h($values["prep_time"]) ?>"
                       required min="1" max="1440" step="1">
                <?php if (!empty($errors["prep_time"])): ?><span class="error-message"><?= h($errors["prep_time"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="servings">Servings</label>
                <input type="number" id="servings" name="servings"
                       value="<?= h($values["servings"]) ?>"
                       required min="1" max="100" step="1">
                <?php if (!empty($errors["servings"])): ?><span class="error-message"><?= h($errors["servings"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty" required>
                    <?php foreach ($allowed_difficulty as $d): ?>
                        <option value="<?= $d ?>" <?= ($values["difficulty"] === $d) ? "selected" : "" ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field full">
                <label for="ingredients">Ingredients (one per line)</label>
                <textarea id="ingredients" name="ingredients" rows="6" required minlength="3" maxlength="3000"><?= h($values["ingredients"]) ?></textarea>
                <?php if (!empty($errors["ingredients"])): ?><span class="error-message"><?= h($errors["ingredients"]) ?></span><?php endif; ?>
            </div>

            <div class="field full">
                <label for="instructions">Instructions (one step per line)</label>
                <textarea id="instructions" name="instructions" rows="8" required minlength="3" maxlength="6000"><?= h($values["instructions"]) ?></textarea>
                <?php if (!empty($errors["instructions"])): ?><span class="error-message"><?= h($errors["instructions"]) ?></span><?php endif; ?>
            </div>

            <div class="field full">
                <label for="image">Photo</label>
                <?php if (!empty($recipe["image"]) && file_exists("images/" . $recipe["image"])): ?>
                    <div class="current-photo">
                        <img src="images/<?= h($recipe["image"]) ?>" alt="current photo">
                        <span class="hint">Current photo &mdash; upload a new file below to replace it.</span>
                    </div>
                <?php else: ?>
                    <span class="hint">No photo yet. Upload one below.</span>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if (!empty($errors["image"])): ?><span class="error-message"><?= h($errors["image"]) ?></span><?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save changes</button>
                <a href="details.php?id=<?= (int)$recipe["recipe_id"] ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    <script src="scripts/recipe_form_validator.js"></script>
</body>
</html>
