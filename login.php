<?php
// User login - displays form on GET and verifies credentials on POST
require("db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login === "" || $password === "") {
        $error = "Please provide both login and password.";
    } else {
        // Look up the user by login (never by password)
        $stmt = $conn->prepare("SELECT id, login, password, is_admin FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Verify the plain password against the stored hash
        if ($row && password_verify($password, $row["password"])) {
            $_SESSION["login"] = $row["login"];
            $_SESSION["is_admin"] = (int)$row["is_admin"];
            $_SESSION["user_id"] = (int)$row["id"];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid login or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container narrow">
        <h1>Log in</h1>

        <?php if ($error !== ""): ?>
            <div class="message error">
                <p><?php echo htmlspecialchars($error); ?></p>
                <p><a href="login.php">Try again</a></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" novalidate>
            <label for="login">Login</label>
            <input type="text" id="login" name="login" required minlength="3" maxlength="50">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="1">

            <button type="submit" class="btn primary">Log in</button>
        </form>

        <p class="auth-link">No account yet? <a href="registration.php">Register</a></p>
    </div>
</body>
</html>
