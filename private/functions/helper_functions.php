<?php
// project-root/private/functions/helper_functions.php

if (!function_exists('h')) {
    function h(string $s = ""): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('u')) {
    function u(string $s = ""): string {
        return urlencode($s);
    }
}

if (!function_exists('raw_u')) {
    function raw_u(string $s = ""): string {
        return rawurlencode($s);
    }
}

if (!function_exists('url_for')) {
    function url_for(string $script_path): string {
        // ensure leading slash
        if ($script_path === '' || $script_path[0] !== '/') {
            $script_path = '/' . $script_path;
        }
        return rtrim(WWW_ROOT, '/') . $script_path;
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to(string $location): void {
        header("Location: " . $location);
        exit;
    }
}

if (!function_exists('is_post_request')) {
    function is_post_request(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

if (!function_exists('is_get_request')) {
    function is_get_request(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

if (!function_exists('display_session_message')) {
    function display_session_message(): string {
        if (isset($_SESSION['message']) && $_SESSION['message'] !== '') {
            $msg = h($_SESSION['message']);
            unset($_SESSION['message']);
            return "<div class=\"session-message\">{$msg}</div>";
        }
        return '';
    }
}
