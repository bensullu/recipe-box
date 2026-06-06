<?php
// Returns raw HTML for the live search results on the home page.
require("db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: text/html; charset=UTF-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo "<p class=\"message error\">Please log in again.</p>";
    exit;
}

$search_query = trim($_GET["search_query"] ?? "");

if (mb_strlen($search_query) < 2) {
    echo "";
    exit;
}

$is_admin = !empty($_SESSION["is_admin"]) && (int)$_SESSION["is_admin"] === 1;

// Match the phrase against title, category name and ingredients
$search_like = "%" . $search_query . "%";
$stmt = $conn->prepare(
    "SELECT r.recipe_id, r.title, r.prep_time, r.servings, r.difficulty, c.name AS category, r.image,
            ROUND(AVG(cm.rating), 1) AS avg_rating,
            COUNT(cm.comment_id) AS comment_count
     FROM recipes r
     INNER JOIN categories c ON c.category_id = r.category_id
     LEFT JOIN comments cm ON cm.recipe_id = r.recipe_id
     WHERE r.title LIKE ? OR c.name LIKE ? OR r.ingredients LIKE ?
     GROUP BY r.recipe_id
     ORDER BY r.title ASC"
);

if (!$stmt) {
    http_response_code(500);
    echo "<p class=\"message error\">Search is unavailable right now.</p>";
    exit;
}

$stmt->bind_param("sss", $search_like, $search_like, $search_like);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "<p>No recipes matched your search.</p>";
    $stmt->close();
    exit;
}
?>
<section class="recipe-grid">
    <?php while ($recipe = $result->fetch_assoc()): ?>
        <?php include("partials/recipe_card.php"); ?>
    <?php endwhile; ?>
</section>
<?php $stmt->close(); ?>
