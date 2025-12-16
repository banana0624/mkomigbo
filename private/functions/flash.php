<?php
declare(strict_types=1);

/**
 * project-root/private/functions/flash.php
 * Simple session-based flash messages.
 *
 * Usage:
 *   flash('success', 'Page created successfully'); // set
 *   flash('error', 'Something went wrong');        // set
 *
 *   echo render_flashes(); // output & clear
 */

if (!function_exists('flash')) {
    /**
     * Set or get flash messages for a given type.
     *
     * flash('success', 'Message');   // add one
     * $msgs = flash('success');      // get (does NOT clear)
     *
     * @param string      $type
     * @param string|null $message
     * @return array<int,string>      list of messages for this type
     */
    function flash(string $type, ?string $message = null): array {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
            // Normalise storage
            $_SESSION['flash'] = [];
        }

        // Setter
        if ($message !== null && $message !== '') {
            if (!isset($_SESSION['flash'][$type])) {
                $_SESSION['flash'][$type] = [];
            } elseif (!is_array($_SESSION['flash'][$type])) {
                // If it somehow became a string, wrap it
                $_SESSION['flash'][$type] = [ (string)$_SESSION['flash'][$type] ];
            }

            $_SESSION['flash'][$type][] = $message;
        }

        // Getter (does not clear)
        $messages = $_SESSION['flash'][$type] ?? [];

        if (!is_array($messages)) {
            $messages = [ (string)$messages ];
        }

        return $messages;
    }
}

if (!function_exists('flash_all')) {
    /**
     * Get all flash messages and clear them from the session.
     *
     * @return array<string, array<int,string>>
     */
    function flash_all(): array {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $raw = $_SESSION['flash'] ?? [];

        // Normalise structure: always [type => [msg, msg2, ...]]
        if (!is_array($raw)) {
            $raw = ['info' => [ (string)$raw ]];
        } else {
            foreach ($raw as $type => $msgs) {
                if (is_string($msgs)) {
                    $raw[$type] = [ $msgs ];
                } elseif (!is_array($msgs)) {
                    $raw[$type] = [ (string)$msgs ];
                }
            }
        }

        // Clear after reading
        $_SESSION['flash'] = [];

        return $raw;
    }
}

if (!function_exists('render_flashes')) {
    /**
     * Render all flash messages as HTML and clear them.
     *
     * @return string
     */
    function render_flashes(): string {
        $flashes = flash_all();
        if (empty($flashes)) {
            return '';
        }

        // Local escape helper if global h() is not available for some reason.
        $esc = function ($v): string {
            if (function_exists('h')) {
                return h((string)$v);
            }
            return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        ob_start();

        foreach ($flashes as $type => $messages) {
            // Ensure $messages is always an array:
            if (!is_array($messages)) {
                $messages = [ (string)$messages ];
            }

            foreach ($messages as $msg) {
                if ($msg === '' || $msg === null) {
                    continue;
                }

                $class = 'flash flash--' . $esc($type);
                ?>
                <div class="<?= $class; ?>">
                    <?= $esc($msg); ?>
                </div>
                <?php
            }
        }

        return (string)ob_get_clean();
    }
}
