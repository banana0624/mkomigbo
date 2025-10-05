<?php
// project-root/private/tools/sync_subjects_registry.php

require_once __DIR__ . '/../assets/config.php';
require_once __DIR__ . '/../assets/database.php';
require_once __DIR__ . '/../assets/subject_registry.php';

$db = db_connect();

// Load registry canonical
$canonical = $SUBJECTS;

// Load DB subjects
$db_subjects = [];
$sql = "SELECT id, name, slug, meta_description, meta_keywords FROM subjects ORDER BY id ASC";
if ($result = mysqli_query($db, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $db_subjects[$row['id']] = $row;
    }
    mysqli_free_result($result);
}

// Prepare merged registry
$merged = $canonical;
$syncLog = [];

// Sync DB → registry (merge new/updated fields)
foreach ($db_subjects as $id => $row) {
    if (!isset($merged[$id])) {
        $merged[$id] = [
            'name' => $row['name'],
            'slug' => $row['slug'],
            'meta_description' => $row['meta_description'],
            'meta_keywords' => $row['meta_keywords'],
        ];
        $syncLog[] = "[TO_REGISTRY] Add subject id=$id from DB";
    } else {
        foreach (['name','slug','meta_description','meta_keywords'] as $f) {
            if ($merged[$id][$f] !== $row[$f]) {
                $syncLog[] = "[TO_REGISTRY] Update registry id=$id field=$f: '{$merged[$id][$f]}' → '{$row[$f]}'";
                $merged[$id][$f] = $row[$f];
            }
        }
    }
}

// Sync registry → DB (insert/update DB rows)
foreach ($canonical as $id => $info) {
    if (!isset($db_subjects[$id])) {
        $syncLog[] = "[TO_DB] Insert into DB id=$id, name={$info['name']}";
    } else {
        foreach (['name','slug','meta_description','meta_keywords'] as $f) {
            if ($db_subjects[$id][$f] !== $info[$f]) {
                $syncLog[] = "[TO_DB] Update DB id=$id field=$f: '{$db_subjects[$id][$f]}' → '{$info[$f]}'";
            }
        }
    }
}

// Path to registry file
$registry_path = PRIVATE_PATH . '/assets/subject_registry.php';

// Backup and write functions
function backup_registry($path) {
    $timestamp = date('Ymd_His');
    $backup = $path . '.bak_' . $timestamp;
    copy($path, $backup);
    return $backup;
}
function write_registry($path, $data) {
    file_put_contents($path, $data, LOCK_EX);
}

// Build registry export text
$export = "<?php\n// Auto-synced subject registry\n\n";
$export .= "\$SUBJECTS = [\n";
foreach ($merged as $id => $info) {
    $export .= "  " . var_export($id, true) . " => [\n";
    foreach ($info as $k => $v) {
        $export .= "    " . var_export($k, true) . " => " . var_export($v, true) . ",\n";
    }
    $export .= "  ],\n";
}
$export .= "];\n";

// Check mode: dry-run or commit
$doCommit = (PHP_SAPI === 'cli' && in_array('--commit', $argv)) || (isset($_GET['commit']) && $_GET['commit'] == '1');

echo "=== Subject Registry Sync Report ===\n";
if (empty($syncLog)) {
    echo "No changes needed. Registry and DB already in sync.\n";
} else {
    foreach ($syncLog as $line) {
        echo $line . "\n";
    }
}

if ($doCommit) {
    // Backup original
    $backup = backup_registry($registry_path);
    echo "Backup of original registry made at: $backup\n";
    // Write new registry
    write_registry($registry_path, $export);
    echo "Registry file written.\n";

    // Perform DB updates/inserts now
    // (Re-execute DB sync for actual updates)
    foreach ($canonical as $id => $info) {
        if (!isset($db_subjects[$id])) {
            $stmt = $db->prepare("INSERT INTO subjects (id, name, slug, meta_description, meta_keywords) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $id, $info['name'], $info['slug'], $info['meta_description'], $info['meta_keywords']);
            $stmt->execute();
        } else {
            foreach (['name','slug','meta_description','meta_keywords'] as $f) {
                if ($db_subjects[$id][$f] !== $info[$f]) {
                    $stmt = $db->prepare("UPDATE subjects SET $f = ? WHERE id = ?");
                    $stmt->bind_param("si", $info[$f], $id);
                    $stmt->execute();
                }
            }
        }
    }
    echo "Database updates applied.\n";
} else {
    echo "\nDRY RUN mode — no files or DB changes applied.\n";
    echo "Run again with `--commit` (CLI) or `?commit=1` (web) to apply.\n";
}

db_disconnect($db);
