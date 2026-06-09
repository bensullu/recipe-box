<?php
// Recipe list - main page available to all logged-in users.
// Supports live AJAX search and category filtering (via links AND a dropdown).
require("session_optional.php");
require("db.php");

$is_admin = !empty($_SESSION["is_admin"]) && (int)$_SESSION["is_admin"] === 1;

// Read the optional category filter from the query string (0 = all)
$active_category = isset($_GET["category"]) ? (int)$_GET["category"] : 0;

// Load all categories for the filter bar and dropdown
$categories = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC");

// Build the recipe query, optionally filtered by category
$sql =
    "SELECT r.recipe_id, r.title, r.prep_time, r.servings, r.difficulty, c.name AS category, r.image,
            ROUND(AVG(cm.rating), 1) AS avg_rating,
            COUNT(cm.comment_id) AS comment_count
     FROM recipes r
     INNER JOIN categories c ON c.category_id = r.category_id
     LEFT JOIN comments cm ON cm.recipe_id = r.recipe_id";

if ($active_category > 0) {
    $sql .= " WHERE r.category_id = ?";
}
$sql .= " GROUP BY r.recipe_id ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
if ($active_category > 0) {
    $stmt->bind_param("i", $active_category);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <section class="hero">
            <div class="hero-inner">
                <p class="hero-eyebrow">Authentic Turkish Cuisine</p>
                <h1 class="hero-title">Cook the classics, from menemen to baklava</h1>
                <p class="hero-sub">Browse hearty kebabs, comforting soups and syrupy desserts &mdash; search, filter by category and save your favorites.</p>
            </div>
        </section>

        <?php if ($is_admin): ?>
            <p class="actions">
                <a href="insert_recipe.php" class="btn primary">+ Add new recipe</a>
            </p>
        <?php endif; ?>

        <!-- Live AJAX search -->
        <div class="search-panel">
            <label for="search-query">Live search</label>
            <input type="text"
                   id="search-query"
                   class="search-input"
                   placeholder="Type at least 2 characters (title, category, ingredient)">
        </div>

        <!-- Category filter as links (point 4: filtering by links) -->
        <div class="filter-bar">
            <a href="index.php" class="chip <?php echo $active_category === 0 ? "is-active" : ""; ?>">All</a>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="index.php?category=<?php echo (int)$cat["category_id"]; ?>"
                   class="chip <?php echo $active_category === (int)$cat["category_id"] ? "is-active" : ""; ?>">
                    <?php echo htmlspecialchars($cat["name"]); ?>
                </a>
            <?php endwhile; ?>
        </div>

        <!-- Category filter as a dropdown (point 4: filtering by a drop-down list) -->
        <div class="filter-select">
            <label for="category-select">Filter by category:</label>
            <select id="category-select">
                <option value="0">-- all categories --</option>
                <?php
                $categories->data_seek(0);
                while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo (int)$cat["category_id"]; ?>"
                        <?php echo $active_category === (int)$cat["category_id"] ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($cat["name"]); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="search-results" class="search-results" aria-live="polite"></div>

        <div id="default-recipe-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <section class="recipe-grid">
                    <?php while ($recipe = $result->fetch_assoc()): ?>
                        <?php include("partials/recipe_card.php"); ?>
                    <?php endwhile; ?>
                </section>
            <?php else: ?>
                <p>No recipes found.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php require("partials/footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="scripts/app.js"></script>
</body>
</html>
<?php $stmt->close(); ?>
