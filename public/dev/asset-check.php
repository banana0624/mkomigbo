<?php
// project-root/public/dev/asset-check.php
declare(strict_types=1);
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
require $init;

$rel = '/lib/images/logo.svg'; // or your png
echo '<pre>';
echo "asset_exists($rel): " . (asset_exists($rel) ? 'YES' : 'NO') . PHP_EOL;
echo "asset_url($rel): " . asset_url($rel) . PHP_EOL;
echo "url_for($rel): " . url_for($rel) . PHP_EOL;
echo '</pre>';
echo '<img src="'.h(asset_url($rel)).'" style="height:40px">';
