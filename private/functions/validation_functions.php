<?php
declare(strict_types=1);

/**
 * Reusable validators and error formatting.
 */

function v_presence(string $val): bool { return trim($val) !== ''; }
function v_minlen(string $val, int $n): bool { return mb_strlen($val) >= $n; }
function v_maxlen(string $val, int $n): bool { return mb_strlen($val) <= $n; }
function v_email(string $val): bool { return (bool)filter_var($val, FILTER_VALIDATE_EMAIL); }
function v_slug(string $val): bool { return (bool)preg_match('/^[a-z0-9-]+$/', $val); }

function errors_has(array $errors): bool { return !empty($errors); }

function errors_html(array $errors): string {
    if (!$errors) return '';
    $out = '<ul class="form-errors">';
    foreach ($errors as $k => $v) {
        $msg = is_array($v) ? implode(', ', $v) : (string)$v;
        $out .= '<li>'.htmlspecialchars(($k !== '_' ? "{$k}: " : '').$msg, ENT_QUOTES, 'UTF-8').'</li>';
    }
    $out .= '</ul>';
    return $out;
}
