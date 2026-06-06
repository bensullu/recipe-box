<?php
// Reusable recipe card. Expects:
//   $recipe   - associative array with recipe fields + avg_rating + comment_count
//   $is_admin - bool (admin sees Edit/Delete buttons)
// Used by index.php and search_ajax.php so the markup stays in one place.
if (!isset($is_admin)) {
    $is_admin = !empty($_SESSION["is_admin"]) && (int)$_SESSION["is_admin"] === 1;
}
?>
<article class="recipe-card">
    <div class="recipe-card-photo">
        <?php if (!empty($recipe["image"]) && file_exists("images/" . $recipe["image"])): ?>
            <img src="images/<?php echo htmlspecialchars($recipe["image"]); ?>"
                 alt="<?php echo htmlspecialchars($recipe["title"]); ?>">
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
            <span>&middot;</span>
            <span>🍽 <?php echo (int)$recipe["servings"]; ?></span>
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
        <a href="details.php?id=<?php echo (int)$recipe["recipe_id"]; ?>" class="btn primary">View</a>
        <?php if ($is_admin): ?>
            <a href="edit.php?id=<?php echo (int)$recipe["recipe_id"]; ?>" class="btn warning">Edit</a>
            <a href="delete.php?id=<?php echo (int)$recipe["recipe_id"]; ?>"
               class="btn danger"
               onclick="return confirm('Delete this recipe?');">Delete</a>
        <?php endif; ?>
    </div>
</article>
