<?php
// project-root/public/staff/admins/edit.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$page_title = 'Edit Admin';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$id = $_GET['id'] ?? '';
$admin = null;
if (function_exists('find_admin_by_id')) {
    $admin = find_admin_by_id($id);
}

if (!$admin) {
    echo "<p>Admin not found.</p>";
} else {
    if (is_post_request()) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        // Optionally handle password change
        $args = [
            'username' => $username,
            'email' => $email,
            // 'password' => ...
        ];
        if (function_exists('update_admin')) {
            $res = update_admin($id, $args);
            if ($res) {
                redirect_to(url_for('/staff/admins/'));
            }
        }
    }
    ?>
    <h2>Edit Admin</h2>
    <form action="<?php echo url_for('/staff/admins/edit.php?id=' . u($id)); ?>" method="post">
      <label for="username">Username:<br>
        <input type="text" name="username" id="username" value="<?php echo h($admin['username']); ?>" required>
      </label><br><br>
      <label for="email">Email:<br>
        <input type="email" name="email" id="email" value="<?php echo h($admin['email']); ?>" required>
      </label><br><br>
      <button type="submit">Update Admin</button>
    </form>
    <p><a href="<?php echo url_for('/staff/admins/'); ?>">Back to list</a></p>
    <?php
}

include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
