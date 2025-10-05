<?php
// project-root/public/signup.php

require_once('../private/assets/initialize.php');

if(is_post_request()) {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'contributor'; // default contributor
    $name     = $_POST['name'] ?? '';
    $bio      = $_POST['bio'] ?? '';

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if($role === 'contributor') {
        $sql = "INSERT INTO contributors (username, email, password_hash, name, bio, created_at) ";
        $sql .= "VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = db_prepare($sql);
        mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $password_hash, $name, $bio);
        mysqli_stmt_execute($stmt);
    } elseif($role === 'admin') {
        $sql = "INSERT INTO admins (username, email, password_hash, created_at) ";
        $sql .= "VALUES (?, ?, ?, NOW())";
        $stmt = db_prepare($sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
        mysqli_stmt_execute($stmt);
    }

    $_SESSION['message'] = "Signup successful. You can now log in.";
    redirect_to(url_for('/login.php'));
}
?>

<?php include(SHARED_PATH . '/public_header.php'); ?>
<h2>Signup</h2>
<form action="" method="post">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Role: 
        <select name="role">
            <option value="contributor" selected>Contributor</option>
            <option value="admin">Admin</option>
        </select>
    </label><br>
    <label>Name (Contributors only): <input type="text" name="name"></label><br>
    <label>Bio (Contributors only): <textarea name="bio"></textarea></label><br>
    <button type="submit">Sign Up</button>
</form>
<?php include(SHARED_PATH . '/public_footer.php'); ?>
