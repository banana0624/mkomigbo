<?php
declare(strict_types=1);

/**
 * project-root/private/functions/registry_functions.php
 *
 * Back-compat wrappers that delegate to the new Registry API.
 * New registry (in /private/registry/subjects_register.php) defines:
 *  - subjects_all(), subjects_sorted(), subject_by_id(), subject_by_slug()
 */

function subject_registry_all(): array {
    if (function_exists('subjects_sorted')) {
        // preferred: sorted by nav_order
        return subjects_sorted();
    }
    if (function_exists('subjects_all')) {
        return subjects_all();
    }
    return [];
}

function subject_registry_get(int $id): ?array {
    if (function_exists('subject_by_id')) {
        return subject_by_id($id);
    }
    // fallback for extremely old code paths:
    global $SUBJECTS_REGISTRY, $SUBJECTS;
    if (isset($SUBJECTS_REGISTRY[$id])) return $SUBJECTS_REGISTRY[$id];
    if (isset($SUBJECTS[$id])) return $SUBJECTS[$id];
    return null;
}

function subject_registry_find_id_by_slug(string $slug): ?int {
    if (function_exists('subject_by_slug')) {
        $row = subject_by_slug($slug);
        return $row['id'] ?? null;
    }
    // fallback
    global $SUBJECTS_REGISTRY, $SUBJECTS;
    $arr = $SUBJECTS_REGISTRY ?? ($SUBJECTS ?? []);
    foreach ($arr as $id => $info) {
        if (($info['slug'] ?? null) === $slug) return (int)$id;
    }
    return null;
}
