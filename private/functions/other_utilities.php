<?php
declare(strict_types=1);

/**
 * Misc utilities that don’t fit elsewhere.
 */
if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return $text ?: 'n-a';
    }
}

function random_string(int $len = 16): string {
    return substr(bin2hex(random_bytes($len)), 0, $len);
}

function human_date(?string $ts): string {
    if (!$ts) return '';
    $t = strtotime($ts);
    return $t ? date('M j, Y H:i', $t) : $ts;
}
