<?php
declare(strict_types=1);
/**
 * Public footer wrapper.
 * Optionally set $scripts_foot, $footer_note, $footer_extra_html before requiring.
 */
if (!isset($footer_note)) {
  $footer_note = ''; // keep public footer minimal by default
}
require __DIR__ . '/footer.php';
