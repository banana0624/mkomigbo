<?php
// project-root/public/auth/signup.php

require_once __DIR__ . '/../../private/assets/config.php';
require_once __DIR__ . '/../../private/assets/database.php';
require_once __DIR__ . '/../../private/assets/auth_functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
    } elseif (empty($username) || empty($email) || empty($password)) {
        $message = "❌ All fields are required.";
    } else {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users table
        $sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $email, $password_hash, $role);
            $success = $stmt->execute();
            if ($success) {
                $message = "✅ Account created successfully. You can now <a href='" . url_for('/auth/login.php') . "'>login</a>.";
            } else {
                $message = "⚠️ Error: " . h($stmt->error);
            }
            $stmt->close();
        } else {
            $message = "⚠️ Database error: " . h($db->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - MKOMIGBO</title>
</head>
<body>
    <h1>Create an Account</h1>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Username:<br>
            <input type="text" name="username" required>
        </label><br><br>
        <label>Email:<br>
            <input type="email" name="email" required>
        </label><br><br>
        <label>Password:<br>
            <input type="password" name="password" required>
        </label><br><br>
        <label>Confirm Password:<br>
            <input type="password" name="confirm_password" required>
        </label><br><br>
        <label>Role:<br>
            <select name="role" required>
                <option value="viewer">Viewer</option>
                <option value="contributor">Contributor</option>
                <option value="editor">Editor</option>
                <option value="admin">Admin</option>
            </select>
        </label><br><br>
        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="<?php echo url_for('/auth/login.php'); ?>">Login here</a>.</p>
</body>
</html>
