<?php
// Form for adding a new recipe - admin only
require("admin_guard.php");
require("db.php");
require("csrf.php");

// Load categories for the dropdown
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// Pick up validation errors and previously entered values from session flash
$errors = $_SESSION["form_errors"]["recipe_add"] ?? [];
$old = $_SESSION["form_old"]["recipe_add"] ?? [];
unset($_SESSION["form_errors"]["recipe_add"], $_SESSION["form_old"]["recipe_add"]);

$difficulties = ["Easy", "Medium", "Hard"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add recipe - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <h1>Add a new recipe</h1>

        <?php if (!empty($errors["_csrf"])): ?>
            <div class="message error"><?= h($errors["_csrf"]) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors["_db"])): ?>
            <div class="message error"><?= h($errors["_db"]) ?></div>
        <?php endif; ?>

        <form method="POST" action="insert.php" enctype="multipart/form-data" class="recipe-form" id="recipe-add-form" novalidate>
            <?php csrf_field("recipe_add"); ?>

            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title"
                       value="<?= h($old["title"] ?? "") ?>"
                       required minlength="1" maxlength="150">
                <?php if (!empty($errors["title"])): ?><span class="error-message"><?= h($errors["title"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- select category --</option>
                    <?php while ($c = $categories_result->fetch_assoc()): ?>
                        <option value="<?= (int)$c["id"] ?>" <?= ((int)($old["category_id"] ?? 0) === (int)$c["id"]) ? "selected" : "" ?>>
                            <?= h($c["name"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if (!empty($errors["category_id"])): ?><span class="error-message"><?= h($errors["category_id"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="prep_time">Preparation time (minutes)</label>
                <input type="number" id="prep_time" name="prep_time"
                       value="<?= h($old["prep_time"] ?? "") ?>"
                       required min="1" max="1440" step="1">
                <?php if (!empty($errors["prep_time"])): ?><span class="error-message"><?= h($errors["prep_time"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="servings">Servings</label>
                <input type="number" id="servings" name="servings"
                       value="<?= h($old["servings"] ?? "") ?>"
                       required min="1" max="100" step="1">
                <?php if (!empty($errors["servings"])): ?><span class="error-message"><?= h($errors["servings"]) ?></span><?php endif; ?>
            </div>

            <div class="field">
                <label for="difficulty">Difficulty</label>
                <select id="difficulty" name="difficulty" required>
                    <?php foreach ($difficulties as $d): ?>
                        <option value="<?= $d ?>" <?= (($old["difficulty"] ?? "Easy") === $d) ? "selected" : "" ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field full">
                <label for="ingredients">Ingredients (one per line)</label>
                <textarea id="ingredients" name="ingredients" rows="6" required minlength="3" maxlength="3000"><?= h($old["ingredients"] ?? "") ?></textarea>
                <?php if (!empty($errors["ingredients"])): ?><span class="error-message"><?= h($errors["ingredients"]) ?></span><?php endif; ?>
            </div>

            <div class="field full">
                <label for="instructions">Instructions (one step per line)</label>
                <textarea id="instructions" name="instructions" rows="8" required minlength="3" maxlength="6000"><?= h($old["instructions"] ?? "") ?></textarea>
                <?php if (!empty($errors["instructions"])): ?><span class="error-message"><?= h($errors["instructions"]) ?></span><?php endif; ?>
            </div>

            <div class="field full">
                <label for="image">Photo (optional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if (!empty($errors["image"])): ?><span class="error-message"><?= h($errors["image"]) ?></span><?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save recipe</button>
                <a href="index.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    <script src="scripts/recipe_form_validator.js"></script>
</body>
</html>
