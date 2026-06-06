<?php
// Shared header with navigation - assumes session is already started by session.php
$current_login = isset($_SESSION["login"]) ? $_SESSION["login"] : "";
$is_admin = !empty($_SESSION["is_admin"]) && (int)$_SESSION["is_admin"] === 1;
$current_page = basename($_SERVER["PHP_SELF"]);
function nav_active($page, $current) {
    return $page === $current ? " active" : "";
}
?>
<header class="site-header">
    <div class="header-inner">
        <div class="brand">
            <a href="index.php">🍳 Recipe Box</a>
        </div>
        <nav class="main-nav">
            <span class="welcome">Hi, <strong><?php echo htmlspecialchars($current_login); ?></strong>!</span>
            <a href="index.php" class="nav-link<?php echo nav_active('index.php', $current_page); ?>">Home</a>
            <a href="favorites.php" class="nav-link<?php echo nav_active('favorites.php', $current_page); ?>">Favorites</a>
            <a href="my_comments.php" class="nav-link<?php echo nav_active('my_comments.php', $current_page); ?>">My comments</a>
            <?php if ($is_admin): ?>
                <a href="insert_recipe.php" class="nav-link admin-link<?php echo nav_active('insert_recipe.php', $current_page); ?>">Add recipe</a>
                <span class="badge">admin</span>
            <?php endif; ?>
            <a href="logout.php" class="nav-link logout">Logout</a>
        </nav>
    </div>
</header>
