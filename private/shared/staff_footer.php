<?php
declare(strict_types=1);
/**
 * Staff footer wrapper.
 * Optionally set $scripts_foot, $footer_note, $footer_extra_html before requiring.
 */
if (!isset($footer_note)) {
  $footer_note = 'Staff area';
}
require __DIR__ . '/footer.php';
