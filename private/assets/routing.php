<?php
declare(strict_types=1);

/**
 * project-root/private/assets/routing.php
 *
 * Routing/response helpers. Kept minimal to avoid clashes with helper_functions.php.
 */

if (!function_exists('redirect_to')) {
    function redirect_to(string $location): never {
        if (function_exists('url_for') && !preg_match('#^https?://#i', $location)) {
            $location = url_for($location);
        }
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('render_404')) {
    /** Render a simple 404 and exit (swap for a template include if desired). */
    function render_404(?string $msg = null): never {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        if ($msg) echo "<p>" . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "</p>";
        exit;
    }
}

if (!function_exists('require_https')) {
    /** Simple guard to require HTTPS for sensitive pages. */
    function require_https(): void {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        if (!$isHttps) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $uri  = $_SERVER['REQUEST_URI'] ?? '/';
            redirect_to("https://{$host}{$uri}");
        }
    }
}
