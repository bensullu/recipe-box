<?php
// User registration - displays form on GET and creates the account on POST
require("db.php");

// Server-side validation with $errors array
$errors = [];
$success = false;
$old = ["login" => "", "email" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";
    $email = trim($_POST["email"] ?? "");
    $old["login"] = $login;
    $old["email"] = $email;

    if ($login === "") {
        $errors["login"] = "Login is required.";
    } elseif (strlen($login) < 3) {
        $errors["login"] = "Login must be at least 3 characters.";
    } elseif (strlen($login) > 50) {
        $errors["login"] = "Login must be at most 50 characters.";
    }

    if ($password === "") {
        $errors["password"] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors["password"] = "Password must be at least 6 characters.";
    }

    if ($email === "") {
        $errors["email"] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email address.";
    } elseif (strlen($email) > 100) {
        $errors["email"] = "Email must be at most 100 characters.";
    }

    if (empty($errors)) {
        // Check whether the login is already taken
        $check = $conn->prepare("SELECT user_id FROM users WHERE login = ?");
        $check->bind_param("s", $login);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors["login"] = "This login is already taken.";
        } else {
            // Hash the password using bcrypt (PASSWORD_DEFAULT)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (login, password, email, is_admin) VALUES (?, ?, ?, 0)"
            );
            $stmt->bind_param("sss", $login, $password_hash, $email);

            if ($stmt->execute()) {
                $success = true;
            } else {
                error_log("Registration insert failed: " . $conn->error);
                $errors["_db"] = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Recipe Box</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container narrow">
        <h1>Create an account</h1>

        <?php if ($success): ?>
            <div class="message success">
                <p>Account created successfully.</p>
                <p><a href="login.php">Go to login page</a></p>
            </div>
        <?php else: ?>
            <?php if (!empty($errors["_db"])): ?>
                <div class="message error"><?= h($errors["_db"]) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" novalidate>
                <label for="login">Login</label>
                <input type="text" id="login" name="login"
                       value="<?= h($old["login"]) ?>"
                       required minlength="3" maxlength="50">
                <?php if (!empty($errors["login"])): ?><span class="error-message"><?= h($errors["login"]) ?></span><?php endif; ?>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
                <?php if (!empty($errors["password"])): ?><span class="error-message"><?= h($errors["password"]) ?></span><?php endif; ?>

                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= h($old["email"]) ?>"
                       required maxlength="100">
                <?php if (!empty($errors["email"])): ?><span class="error-message"><?= h($errors["email"]) ?></span><?php endif; ?>

                <button type="submit" class="btn primary">Register</button>
            </form>

            <p class="auth-link">Already have an account? <a href="login.php">Log in</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
