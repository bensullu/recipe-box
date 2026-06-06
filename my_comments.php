<?php
// Shows the comments submitted by the currently logged-in user
require("session.php");
require("db.php");

$user_id = (int)$_SESSION["user_id"];

// Join comments with users and recipes to keep ownership checks tied to the user id.
$stmt = $conn->prepare(
    "SELECT cm.comment_id, cm.recipe_id, cm.rating, cm.content, cm.created_at, r.title
     FROM comments cm
     INNER JOIN recipes r ON r.recipe_id = cm.recipe_id
     INNER JOIN users u ON u.login = cm.username
     WHERE u.user_id = ?
     ORDER BY cm.created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Compute the user's own average rating across all of their comments.
$avg_stmt = $conn->prepare(
    "SELECT COUNT(*) AS comment_count, AVG(cm.rating) AS avg_rating
     FROM comments cm
     INNER JOIN users u ON u.login = cm.username
     WHERE u.user_id = ?"
);
$avg_stmt->bind_param("i", $user_id);
$avg_stmt->execute();
$avg_stats = $avg_stmt->get_result()->fetch_assoc();
$avg_stmt->close();

$has_comments = $result && $result->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My comments - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <h1>My comments</h1>

        <?php if ((int)$avg_stats["comment_count"] > 0): ?>
            <div class="rating-summary">
                <span class="rating-big">★ <?php echo number_format((float)$avg_stats["avg_rating"], 1); ?></span>
                <span class="rating-scale">/ 5</span>
                <small>your average across <?php echo (int)$avg_stats["comment_count"]; ?>
                    comment<?php echo ((int)$avg_stats["comment_count"] === 1 ? "" : "s"); ?></small>
            </div>
        <?php endif; ?>

        <p id="comments-empty-state" class="<?php echo $has_comments ? "is-hidden" : ""; ?>">You have not submitted any comments yet.</p>

        <?php if ($has_comments): ?>
            <ul class="reviews-list">
                <?php while ($comment = $result->fetch_assoc()): ?>
                    <li class="review-item" id="comment-<?php echo (int)$comment["comment_id"]; ?>" data-comment-item="1">
                        <div class="review-head">
                            <span class="date"><?php echo htmlspecialchars($comment["created_at"]); ?></span>
                            <a href="details.php?id=<?php echo (int)$comment["recipe_id"]; ?>">
                                <strong><?php echo htmlspecialchars($comment["title"]); ?></strong>
                            </a>
                            <span class="rating"><?php echo str_repeat("★", (int)$comment["rating"]) . str_repeat("☆", 5 - (int)$comment["rating"]); ?></span>
                            <div class="review-actions">
                                <button type="button"
                                        class="btn danger delete-comment"
                                        data-comment-id="<?php echo (int)$comment["comment_id"]; ?>">
                                    Delete comment
                                </button>
                            </div>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($comment["content"])); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <p><a href="index.php" class="btn">Back to recipes</a></p>
    </div>

    <?php require("partials/footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="scripts/app.js"></script>
</body>
</html>
<?php $stmt->close(); ?>
