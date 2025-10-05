<?php
// project-root/public/staff/admins/new.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$page_title = 'New Admin';
include_once PRIVATE_PATH . '/shared/staff_header.php';

if (is_post_request()) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // You may want validation here
    if (function_exists('create_admin')) {
        $args = [
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];
        $new_id = create_admin($args);
        if ($new_id) {
            redirect_to(url_for('/staff/admins/'));
        }
    }
}
?>

<h2>Add New Admin</h2>
<form action="<?php echo url_for('/staff/admins/new.php'); ?>" method="post">
  <label for="username">Username:<br>
    <input type="text" name="username" id="username" required>
  </label><br><br>
  <label for="email">Email:<br>
    <input type="email" name="email" id="email" required>
  </label><br><br>
  <label for="password">Password:<br>
    <input type="password" name="password" id="password" required>
  </label><br><br>
  <button type="submit">Create Admin</button>
</form>

<p><a href="<?php echo url_for('/staff/admins/'); ?>">Back to list</a></p>

<?php
include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
