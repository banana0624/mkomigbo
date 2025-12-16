<?php
// project-root/public/test_jfif_upload.php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  echo '<pre>';
  var_dump($_FILES['image'] ?? null);
  echo '</pre>';
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>JFIF Upload Test</title>
</head>
<body>
  <h1>Test JFIF Upload</h1>
  <form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="image" accept=".jpg,.jpeg,.jfif,.png,.gif,.avif,.webp">
    <button type="submit">Upload</button>
  </form>
</body>
</html>
