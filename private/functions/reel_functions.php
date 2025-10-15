<?php
declare(strict_types=1);

/**
 * Reels are a filtered view on videos (kind='reel').
 */

require_once __DIR__ . '/video_functions.php';

function list_reels(): array {
    return list_videos(['kind' => 'reel']);
}
function create_reel(array $args): array {
    $args['kind'] = 'reel';
    return create_video($args);
}
