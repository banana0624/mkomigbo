<?php
declare(strict_types=1);

/**
 * SEO helpers that consume the templates from /private/registry/seo_templates.php.
 */

function seo_build(string $templateKey, array $vars): array {
    if (!function_exists('seo_template')) return [];
    $tpl = seo_template($templateKey);
    if (!$tpl) return [];

    // shallow replace tokens {token}
    $repl = function($s) use ($vars) {
        return preg_replace_callback('/\{([a-z0-9_]+)\}/i', function($m) use ($vars) {
            $k = $m[1];
            return isset($vars[$k]) ? (string)$vars[$k] : '';
        }, (string)$s);
    };

    $out = [
        'title'       => $repl($tpl['title'] ?? ''),
        'description' => $repl($tpl['description'] ?? ''),
        'keywords'    => $repl($tpl['keywords'] ?? ''),
        'canonical'   => $repl($tpl['canonical'] ?? ''),
        'og'          => [],
    ];
    if (!empty($tpl['og'])) {
        foreach ($tpl['og'] as $k => $v) { $out['og'][$k] = $repl($v); }
    }
    return $out;
}

/** Optional: render `<meta>` tags block */
function seo_html(array $meta): string {
    $lines = [];
    if (!empty($meta['title']))       $lines[] = '<title>'.htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8').'</title>';
    if (!empty($meta['description'])) $lines[] = '<meta name="description" content="'.htmlspecialchars($meta['description'],ENT_QUOTES,'UTF-8').'">';
    if (!empty($meta['keywords']))    $lines[] = '<meta name="keywords" content="'.htmlspecialchars($meta['keywords'],ENT_QUOTES,'UTF-8').'">';
    if (!empty($meta['canonical']))   $lines[] = '<link rel="canonical" href="'.htmlspecialchars($meta['canonical'],ENT_QUOTES,'UTF-8').'">';
    if (!empty($meta['og'])) {
        foreach ($meta['og'] as $k=>$v) {
            $lines[] = '<meta property="og:'.htmlspecialchars($k,ENT_QUOTES,'UTF-8').'" content="'.htmlspecialchars($v,ENT_QUOTES,'UTF-8').'">';
        }
    }
    return implode("\n", $lines)."\n";
}
