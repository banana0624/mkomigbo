<?php
declare(strict_types=1);

/**
 * project-root/private/common/pagination.php
 * Tiny pager helper for lists.
 * Usage:
 *   [$limit,$offset,$page] = pager_input($_GET, 25);
 *   echo pager_render($total, $page, $limit, url_for('/path') . '?' . http_build_query($keep));
 */

if (!function_exists('pager_input')) {
  function pager_input(array $src, int $default = 25): array {
    $page  = max(1, (int)($src['page'] ?? 1));
    $limit = max(1, min(200, (int)($src['limit'] ?? $default)));
    $offset = ($page - 1) * $limit;
    return [$limit, $offset, $page];
  }
}

if (!function_exists('pager_render')) {
  function pager_render(int $total, int $page, int $limit, string $baseUrl): string {
    $pages = (int)max(1, ceil($total / max(1,$limit)));
    if ($pages <= 1) return '';
    $mk = function(int $p) use ($baseUrl, $limit) {
      $glue = (str_contains($baseUrl,'?') ? '&' : '?');
      return $baseUrl . $glue . 'page=' . $p . '&limit=' . $limit;
    };
    $html = '<nav class="pager" style="margin:.75rem 0;display:flex;gap:.375rem;flex-wrap:wrap">';
    $html .= '<a class="btn" href="'.h($mk(max(1,$page-1))).'">&laquo; Prev</a>';
    for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++) {
      $cls = $p === $page ? 'btn btn-primary' : 'btn';
      $html .= '<a class="'.$cls.'" href="'.h($mk($p)).'">'.(int)$p.'</a>';
    }
    $html .= '<a class="btn" href="'.h($mk(min($pages,$page+1))).'">Next &raquo;</a>';
    $html .= '</nav>';
    return $html;
  }
}
