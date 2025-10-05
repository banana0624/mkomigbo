<?php
// project-root/public/auth/login.php

require_once __DIR__ . '/../../private/assets/config.php';
require_once __DIR__ . '/../../private/assets/database.php';
require_once __DIR__ . '/../../private/assets/auth_functions.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Look up user
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($password, $user['password_hash'])) {
        log_in_user($user);
        header("Location: " . url_for('/auth/welcome.php'));
        exit;
    } else {
        $message = "❌ Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MKOMIGBO</title>
</head>
<body>
    <h1>Login</h1>
    <?php if($message): ?>
        <p style="color:red;"><?php echo h($message); ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label>Email:<br>
            <input type="text" name="email" required>
        </label><br><br>
        <label>Password:<br>
            <input type="password" name="password" required>
        </label><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
