<?php
declare(strict_types=1);

/**
 * project-root/private/tools/path_scanner.php
 * Path / URL scanner for dev.
 *
 * Usage (via public/test/scan_paths.php):
 *   mk_scan_project_for_patterns([...]);
 *
 * It walks the project tree and looks for suspicious patterns, e.g.:
 *   - F:/xampp/...
 *   - F:\xampp\...
 *   - project-root/public/staff/login
 *   - http://localhost
 *
 * It intentionally skips vendor/, storage/, .git/, logs, etc.
 */

if (!function_exists('mkps_base_path')) {
    function mkps_base_path(): string {
        // We are in project-root/private/tools
        return dirname(__DIR__, 1); // project-root/private → project-root
    }
}

if (!function_exists('mkps_should_skip_dir')) {
    function mkps_should_skip_dir(string $path): bool {
        $path = str_replace('\\', '/', $path);

        $skipNames = [
            '/vendor/',
            '/storage/',
            '/logs/',
            '/.git/',
            '/.idea/',
            '/node_modules/',
            '/tmp/',
            '/cache/',
        ];

        foreach ($skipNames as $sn) {
            if (str_contains($path, $sn)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('mkps_is_text_file')) {
    function mkps_is_text_file(string $file): bool {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext === '') return false;

        // Only scan likely text/code files
        $ok = [
            'php','phtml','html','htm','css','js','md','txt','ini','env',
            'xml','json','yml','yaml',
        ];
        return in_array($ext, $ok, true);
    }
}

if (!function_exists('mkps_scan_file')) {
    /**
     * Scan a single file for patterns.
     *
     * @return array<int,array{file:string,line:int,text:string,pattern:string}>
     */
    function mkps_scan_file(string $file, array $patterns): array {
        $out = [];
        $fh = @fopen($file, 'rb');
        if (!$fh) return $out;

        $lineNo = 0;
        while (($line = fgets($fh)) !== false) {
            $lineNo++;
            $check = $line;

            foreach ($patterns as $pattern) {
                if ($pattern === '') continue;
                if (stripos($check, $pattern) !== false) {
                    $out[] = [
                        'file'    => $file,
                        'line'    => $lineNo,
                        'text'    => rtrim($line, "\r\n"),
                        'pattern' => $pattern,
                    ];
                }
            }
        }

        fclose($fh);
        return $out;
    }
}

if (!function_exists('mk_scan_project_for_patterns')) {
    /**
     * Main entry: scan project for dangerous patterns.
     *
     * @param array<int,string> $patterns
     * @return array<int,array{file:string,line:int,text:string,pattern:string}>
     */
    function mk_scan_project_for_patterns(array $patterns): array {
        $base = mkps_base_path(); // project-root
        $baseNorm = rtrim(str_replace('\\', '/', $base), '/') . '/';

        $results = [];

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $info */
        foreach ($it as $info) {
            $path = $info->getPathname();
            $pathNorm = str_replace('\\', '/', $path);

            if ($info->isDir()) {
                if (mkps_should_skip_dir($pathNorm)) {
                    $it->next(); // hint to skip children; not perfect but helps
                }
                continue;
            }

            if (!mkps_is_text_file($path)) {
                continue;
            }

            $fileResults = mkps_scan_file($path, $patterns);
            if ($fileResults) {
                foreach ($fileResults as $r) {
                    // Normalize to project-root relative path for display
                    $r['file'] = ltrim(str_replace('\\', '/', $r['file']), '/');
                    if (str_starts_with($r['file'], $baseNorm)) {
                        $r['file'] = substr($r['file'], strlen($baseNorm));
                    }
                    $results[] = $r;
                }
            }
        }

        return $results;
    }
}
