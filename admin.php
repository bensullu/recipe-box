<?php
// Admin dashboard - statistics, category management and user management (admin only)
require("admin_guard.php");
require("db.php");
require("csrf.php");

// ---------- handle POST actions ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if (!csrf_verify("admin_panel", $_POST["csrf_token"] ?? null)) {
        $_SESSION["admin_msg"] = ["error", "Security token expired. Please try again."];
        header("Location: admin.php");
        exit;
    }
    $me = (int)$_SESSION["user_id"];
    $msg = null;

    if ($action === "add_category") {
        $name = trim($_POST["name"] ?? "");
        if ($name === "" || mb_strlen($name) > 50) {
            $msg = ["error", "Category name must be 1–50 characters."];
        } else {
            $st = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $st->bind_param("s", $name);
            $st->execute();
            $msg = $st->affected_rows > 0 ? ["success", "Category \"$name\" added."] : ["error", "That category already exists."];
            $st->close();
        }
    } elseif ($action === "del_category") {
        $cid = (int)($_POST["category_id"] ?? 0);
        $st = $conn->prepare("SELECT COUNT(*) AS c FROM recipes WHERE category_id = ?");
        $st->bind_param("i", $cid);
        $st->execute();
        $cnt = (int)$st->get_result()->fetch_assoc()["c"];
        $st->close();
        if ($cnt > 0) {
            $msg = ["error", "Cannot delete: this category still has $cnt recipe(s)."];
        } else {
            $st = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $st->bind_param("i", $cid);
            $st->execute();
            $st->close();
            $msg = ["success", "Category deleted."];
        }
    } elseif ($action === "make_admin" || $action === "remove_admin") {
        $uid = (int)($_POST["user_id"] ?? 0);
        $val = $action === "make_admin" ? 1 : 0;
        if ($uid === $me) {
            $msg = ["error", "You cannot change your own role."];
        } else {
            $st = $conn->prepare("UPDATE users SET is_admin = ? WHERE user_id = ?");
            $st->bind_param("ii", $val, $uid);
            $st->execute();
            $st->close();
            $msg = ["success", "User role updated."];
        }
    } elseif ($action === "del_user") {
        $uid = (int)($_POST["user_id"] ?? 0);
        if ($uid === $me) {
            $msg = ["error", "You cannot delete your own account."];
        } else {
            $st = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $st->bind_param("i", $uid);
            $st->execute();
            $st->close();
            $msg = ["success", "User deleted."];
        }
    }
    $_SESSION["admin_msg"] = $msg;
    header("Location: admin.php");
    exit;
}

// ---------- gather data ----------
$stats = $conn->query(
    "SELECT
        (SELECT COUNT(*) FROM recipes)    AS recipes,
        (SELECT COUNT(*) FROM users)      AS users,
        (SELECT COUNT(*) FROM comments)   AS comments,
        (SELECT COUNT(*) FROM categories) AS categories,
        (SELECT ROUND(AVG(rating), 2) FROM comments) AS avg_rating"
)->fetch_assoc();

$top_fav = $conn->query(
    "SELECT r.title, COUNT(f.favorite_id) AS favs
     FROM recipes r LEFT JOIN favorites f ON f.recipe_id = r.recipe_id
     GROUP BY r.recipe_id ORDER BY favs DESC, r.title ASC LIMIT 5"
);

$cats = $conn->query(
    "SELECT c.category_id, c.name, COUNT(r.recipe_id) AS recipe_count
     FROM categories c LEFT JOIN recipes r ON r.category_id = c.category_id
     GROUP BY c.category_id ORDER BY c.name ASC"
);

$users = $conn->query(
    "SELECT user_id, login, email, is_admin, registration_date FROM users ORDER BY is_admin DESC, login ASC"
);

$flash = $_SESSION["admin_msg"] ?? null;
unset($_SESSION["admin_msg"]);
$me = (int)$_SESSION["user_id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin dashboard - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php require("header.php"); ?>

    <div class="container">
        <h1>Admin dashboard</h1>

        <?php if ($flash): ?>
            <div class="message <?= $flash[0] === "success" ? "success" : "error" ?>"><?= h($flash[1]) ?></div>
        <?php endif; ?>

        <!-- statistics -->
        <section class="stat-grid">
            <div class="stat-card"><span class="stat-num"><?= (int)$stats["recipes"] ?></span><span class="stat-label">Recipes</span></div>
            <div class="stat-card"><span class="stat-num"><?= (int)$stats["users"] ?></span><span class="stat-label">Users</span></div>
            <div class="stat-card"><span class="stat-num"><?= (int)$stats["comments"] ?></span><span class="stat-label">Comments</span></div>
            <div class="stat-card"><span class="stat-num"><?= (int)$stats["categories"] ?></span><span class="stat-label">Categories</span></div>
            <div class="stat-card"><span class="stat-num">★ <?= $stats["avg_rating"] !== null ? h($stats["avg_rating"]) : "–" ?></span><span class="stat-label">Avg rating</span></div>
        </section>

        <!-- most favorited -->
        <section class="admin-section">
            <h2>Most favorited recipes</h2>
            <ol class="top-list">
                <?php $any = false; while ($t = $top_fav->fetch_assoc()): if ((int)$t["favs"] === 0) continue; $any = true; ?>
                    <li><span><?= h($t["title"]) ?></span> <strong><?= (int)$t["favs"] ?> ♥</strong></li>
                <?php endwhile; ?>
                <?php if (!$any): ?><li class="muted">No favorites yet.</li><?php endif; ?>
            </ol>
        </section>

        <!-- category management -->
        <section class="admin-section">
            <h2>Categories</h2>
            <form method="POST" action="admin.php" class="inline-form">
                <?php csrf_field("admin_panel"); ?>
                <input type="hidden" name="action" value="add_category">
                <input type="text" name="name" placeholder="New category name" required maxlength="50">
                <button type="submit" class="btn primary">Add category</button>
            </form>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Category</th><th>Recipes</th><th></th></tr></thead>
                    <tbody>
                    <?php while ($c = $cats->fetch_assoc()): ?>
                        <tr>
                            <td><?= h($c["name"]) ?></td>
                            <td><?= (int)$c["recipe_count"] ?></td>
                            <td class="right">
                                <form method="POST" action="admin.php" onsubmit="return confirm('Delete this category?');">
                                    <?php csrf_field("admin_panel"); ?>
                                    <input type="hidden" name="action" value="del_category">
                                    <input type="hidden" name="category_id" value="<?= (int)$c["category_id"] ?>">
                                    <button type="submit" class="btn danger small" <?= (int)$c["recipe_count"] > 0 ? "disabled title='Has recipes'" : "" ?>>Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- user management -->
        <section class="admin-section">
            <h2>Users</h2>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Login</th><th>Email</th><th>Role</th><th>Registered</th><th></th></tr></thead>
                    <tbody>
                    <?php while ($u = $users->fetch_assoc()): $uid = (int)$u["user_id"]; $isAdm = (int)$u["is_admin"] === 1; ?>
                        <tr>
                            <td><?= h($u["login"]) ?><?= $uid === $me ? " <em>(you)</em>" : "" ?></td>
                            <td><?= h($u["email"]) ?></td>
                            <td><?= $isAdm ? '<span class="role-badge">admin</span>' : "user" ?></td>
                            <td><?= h($u["registration_date"]) ?></td>
                            <td class="right">
                                <?php if ($uid !== $me): ?>
                                    <form method="POST" action="admin.php" class="row-actions">
                                        <?php csrf_field("admin_panel"); ?>
                                        <input type="hidden" name="user_id" value="<?= $uid ?>">
                                        <button type="submit" name="action" value="<?= $isAdm ? "remove_admin" : "make_admin" ?>" class="btn warning small"><?= $isAdm ? "Remove admin" : "Make admin" ?></button>
                                        <button type="submit" name="action" value="del_user" class="btn danger small" onclick="return confirm('Delete this user and all their data?');">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <?php require("partials/footer.php"); ?>
</body>
</html>
