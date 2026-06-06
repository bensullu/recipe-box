<?php
// Shows the recipes favorited by the currently logged-in user.
require("session.php");
require("db.php");

$is_admin = !empty($_SESSION["is_admin"]) && (int)$_SESSION["is_admin"] === 1;
$user_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT r.id, r.title, r.prep_time, r.servings, r.difficulty, c.name AS category, r.image,
            ROUND(AVG(cm.rating), 1) AS avg_rating,
            COUNT(cm.id) AS comment_count
     FROM favorites fav
     INNER JOIN recipes r ON r.id = fav.recipe_id
     INNER JOIN categories c ON c.id = r.category_id
     LEFT JOIN comments cm ON cm.recipe_id = r.id
     WHERE fav.user_id = ?
     GROUP BY r.id
     ORDER BY r.title ASC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_favorites = $result && $result->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <h1>My favorite recipes</h1>

        <p id="favorites-empty-state" class="<?php echo $has_favorites ? "is-hidden" : ""; ?>">You have not added any favorite recipes yet.</p>

        <?php if ($has_favorites): ?>
            <section class="recipe-grid" id="favorites-grid">
                <?php while ($recipe = $result->fetch_assoc()): ?>
                    <article class="recipe-card" id="favorite-<?php echo (int)$recipe["id"]; ?>" data-favorite-item="1">
                        <div class="recipe-card-photo">
                            <?php if (!empty($recipe["image"]) && file_exists("images/" . $recipe["image"])): ?>
                                <img src="images/<?php echo htmlspecialchars($recipe["image"]); ?>" alt="<?php echo htmlspecialchars($recipe["title"]); ?>">
                            <?php else: ?>
                                <div class="no-photo">no photo</div>
                            <?php endif; ?>
                            <span class="difficulty-tag diff-<?php echo strtolower(htmlspecialchars($recipe["difficulty"])); ?>">
                                <?php echo htmlspecialchars($recipe["difficulty"]); ?>
                            </span>
                        </div>
                        <div class="recipe-card-body">
                            <h3 class="recipe-card-title"><?php echo htmlspecialchars($recipe["title"]); ?></h3>
                            <p class="recipe-card-meta">
                                <span><?php echo htmlspecialchars($recipe["category"]); ?></span>
                                <span>&middot;</span>
                                <span>⏱ <?php echo (int)$recipe["prep_time"]; ?> min</span>
                            </p>
                            <p class="recipe-card-rating">
                                <?php if ((int)$recipe["comment_count"] > 0): ?>
                                    <span class="rating-value">★ <?php echo number_format((float)$recipe["avg_rating"], 1); ?>/5</span>
                                    <small class="rating-count">(<?php echo (int)$recipe["comment_count"]; ?>)</small>
                                <?php else: ?>
                                    <span class="rating-empty">no ratings yet</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="recipe-card-actions">
                            <a href="details.php?id=<?php echo (int)$recipe["id"]; ?>" class="btn primary">View</a>
                            <button type="button"
                                    class="btn fav-toggle is-liked"
                                    data-recipe-id="<?php echo (int)$recipe["id"]; ?>"
                                    data-liked="1"
                                    aria-pressed="true">
                                Remove
                            </button>
                        </div>
                    </article>
                <?php endwhile; ?>
            </section>
        <?php endif; ?>

        <p><a href="index.php" class="btn">Back to home</a></p>
    </div>

    <?php require("partials/footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="scripts/app.js"></script>
</body>
</html>
<?php $stmt->close(); ?>
