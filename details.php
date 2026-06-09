<?php
// Recipe details with ingredients, instructions, comments and a comment form
// Public page: start the session if needed, but do not force login (guests may browse).
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require("db.php");

$is_admin = !empty($_SESSION["is_admin"]) && (int)$_SESSION["is_admin"] === 1;
$logged_in = isset($_SESSION["user_id"]);
$recipe_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($recipe_id <= 0) {
    header("Location: index.php");
    exit;
}

// Load the recipe (JOIN categories to get the category name + id for the quick-nav link)
$stmt = $conn->prepare(
    "SELECT r.recipe_id, r.title, r.category_id, c.name AS category, r.prep_time, r.servings,
            r.difficulty, r.ingredients, r.instructions, r.image, r.created_at
     FROM recipes r
     INNER JOIN categories c ON c.category_id = r.category_id
     WHERE r.recipe_id = ?"
);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$recipe) {
    header("Location: index.php");
    exit;
}

// Aggregate rating statistics for this recipe (1..5 scale)
$stats_stmt = $conn->prepare(
    "SELECT COUNT(*) AS comment_count, AVG(rating) AS avg_rating, MIN(rating) AS min_rating, MAX(rating) AS max_rating
     FROM comments WHERE recipe_id = ?"
);
$stats_stmt->bind_param("i", $recipe_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

// Has the current user already favorited this recipe?
$is_favorite = false;
if ($logged_in) {
    $favorite_stmt = $conn->prepare("SELECT favorite_id FROM favorites WHERE recipe_id = ? AND user_id = ? LIMIT 1");
    $user_id = (int)$_SESSION["user_id"];
    $favorite_stmt->bind_param("ii", $recipe_id, $user_id);
    $favorite_stmt->execute();
    $is_favorite = $favorite_stmt->get_result()->num_rows > 0;
    $favorite_stmt->close();
}

// Load comments for this recipe
$comments_stmt = $conn->prepare(
    "SELECT cm.comment_id, u.login AS username, cm.rating, cm.content, cm.created_at
     FROM comments cm INNER JOIN users u ON u.user_id = cm.user_id
     WHERE cm.recipe_id = ? ORDER BY cm.created_at DESC"
);
$comments_stmt->bind_param("i", $recipe_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Split ingredients/instructions stored as newline-separated text into arrays
$ingredient_lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $recipe["ingredients"])), 'strlen');
$instruction_lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $recipe["instructions"])), 'strlen');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe["title"]); ?> - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <a href="index.php" class="back-link">&laquo; Back to recipes</a>

        <article class="recipe-details">
            <div class="recipe-photo">
                <?php if (!empty($recipe["image"]) && file_exists("images/" . $recipe["image"])): ?>
                    <img src="images/<?php echo htmlspecialchars($recipe["image"]); ?>" alt="<?php echo htmlspecialchars($recipe["title"]); ?>">
                <?php else: ?>
                    <div class="no-photo large">no photo</div>
                <?php endif; ?>
            </div>
            <div class="recipe-info">
                <h1><?php echo htmlspecialchars($recipe["title"]); ?></h1>
                <p class="recipe-card-meta">
                    <!-- point 6: quick navigation - category name links to the filtered home page -->
                    <strong>Category:</strong>
                    <a href="index.php?category=<?php echo (int)$recipe["category_id"]; ?>" class="category-link">
                        <?php echo htmlspecialchars($recipe["category"]); ?>
                    </a>
                </p>
                <p class="recipe-card-meta">
                    <span>⏱ <?php echo (int)$recipe["prep_time"]; ?> min</span>
                    <span>&middot;</span>
                    <span>🍽 <?php echo (int)$recipe["servings"]; ?> servings</span>
                    <span>&middot;</span>
                    <span>Difficulty: <?php echo htmlspecialchars($recipe["difficulty"]); ?></span>
                </p>

                <?php if ((int)$stats["comment_count"] > 0): ?>
                    <div class="rating-summary">
                        <span class="rating-big">★ <?php echo number_format((float)$stats["avg_rating"], 1); ?></span>
                        <span class="rating-scale">/ 5</span>
                        <small>
                            from <?php echo (int)$stats["comment_count"]; ?>
                            rating<?php echo ((int)$stats["comment_count"] === 1 ? "" : "s"); ?>
                            (min <?php echo (int)$stats["min_rating"]; ?>, max <?php echo (int)$stats["max_rating"]; ?>)
                        </small>
                    </div>
                <?php else: ?>
                    <div class="rating-summary empty">No ratings yet.</div>
                <?php endif; ?>

                <div class="recipe-actions">
                    <?php if ($logged_in): ?>
                        <button type="button"
                                class="btn fav-toggle <?php echo $is_favorite ? "is-liked" : ""; ?>"
                                data-recipe-id="<?php echo (int)$recipe["recipe_id"]; ?>"
                                data-liked="<?php echo $is_favorite ? 1 : 0; ?>"
                                aria-pressed="<?php echo $is_favorite ? "true" : "false"; ?>">
                            <?php echo $is_favorite ? "Remove from favorites" : "Add to favorites"; ?>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn">Log in to save this recipe</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <div class="recipe-columns">
            <section class="ingredients-section">
                <h2>Ingredients</h2>
                <ul class="ingredients-list">
                    <?php foreach ($ingredient_lines as $line): ?>
                        <li><?php echo htmlspecialchars($line); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="instructions-section">
                <h2>Instructions</h2>
                <ol class="instructions-list">
                    <?php foreach ($instruction_lines as $line): ?>
                        <li><?php echo htmlspecialchars($line); ?></li>
                    <?php endforeach; ?>
                </ol>
            </section>
        </div>

        <section class="reviews-section">
            <h2>Comments &amp; ratings</h2>

            <?php if ($comments_result && $comments_result->num_rows > 0): ?>
                <ul class="reviews-list">
                    <?php while ($comment = $comments_result->fetch_assoc()): ?>
                        <li class="review-item" id="comment-<?php echo (int)$comment["comment_id"]; ?>" data-comment-item="1">
                            <div class="review-head">
                                <strong><?php echo htmlspecialchars($comment["username"]); ?></strong>
                                <span class="rating"><?php echo str_repeat("★", (int)$comment["rating"]) . str_repeat("☆", 5 - (int)$comment["rating"]); ?></span>
                                <span class="date"><?php echo htmlspecialchars($comment["created_at"]); ?></span>
                                <?php if ($is_admin): ?>
                                    <div class="review-actions">
                                        <button type="button" class="btn danger small mod-delete-comment"
                                                data-comment-id="<?php echo (int)$comment["comment_id"]; ?>">Delete</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($comment["content"])); ?></p>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No comments yet. Be the first to add one.</p>
            <?php endif; ?>

            <?php if ($logged_in): ?>
            <h3>Add your comment</h3>
            <?php
            require_once("csrf.php");
            $comment_errors = $_SESSION["form_errors"]["comment_add"] ?? [];
            $comment_old = $_SESSION["form_old"]["comment_add"] ?? [];
            unset($_SESSION["form_errors"]["comment_add"], $_SESSION["form_old"]["comment_add"]);
            ?>
            <?php if (!empty($comment_errors["_csrf"])): ?>
                <div class="message error"><?= h($comment_errors["_csrf"]) ?></div>
            <?php endif; ?>
            <form method="POST" action="insert_comment.php" class="review-form" novalidate>
                <input type="hidden" name="recipe_id" value="<?= (int)$recipe["recipe_id"] ?>">
                <?php csrf_field("comment_add"); ?>

                <label for="rating">Rating (1-5)</label>
                <input type="number" id="rating" name="rating"
                       value="<?= h($comment_old["rating"] ?? "") ?>"
                       required min="1" max="5" step="1">
                <?php if (!empty($comment_errors["rating"])): ?><span class="error-message"><?= h($comment_errors["rating"]) ?></span><?php endif; ?>

                <label for="content">Comment</label>
                <textarea id="content" name="content" rows="4" required minlength="5" maxlength="2000"><?= h($comment_old["content"] ?? "") ?></textarea>
                <?php if (!empty($comment_errors["content"])): ?><span class="error-message"><?= h($comment_errors["content"]) ?></span><?php endif; ?>

                <button type="submit" class="btn primary">Submit comment</button>
            </form>
            <p class="hint">Your comment will be signed automatically as
                <strong><?= h($_SESSION["login"]) ?></strong>.</p>
            <?php else: ?>
            <p class="hint"><a href="login.php">Log in</a> or <a href="registration.php">create an account</a> to leave a comment and rating.</p>
            <?php endif; ?>
        </section>
    </div>

    <?php require("partials/footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="scripts/app.js"></script>
</body>
</html>
<?php $comments_stmt->close(); ?>
