<?php
// project-root/private/functions/registry_functions.php

/**
 * Return all subjects sorted by nav_order.
 * @return array<int,array>
 */
function subject_registry_all(): array {
    global $SUBJECTS;
    if (!isset($SUBJECTS) || !is_array($SUBJECTS)) {
        return [];
    }
    uasort($SUBJECTS, function($a, $b) {
        return ($a['nav_order'] <=> $b['nav_order']);
    });
    return $SUBJECTS;
}

/**
 * Get subject metadata by ID.
 * @param int $id
 * @return array|null
 */
function subject_registry_get(int $id): ?array {
    global $SUBJECTS;
    return $SUBJECTS[$id] ?? null;
}

/**
 * Find subject ID by slug.
 * @param string $slug
 * @return int|null
 */
function subject_registry_find_id_by_slug(string $slug): ?int {
    global $SUBJECTS;
    foreach ($SUBJECTS as $id => $info) {
        if (isset($info['slug']) && $info['slug'] === $slug) {
            return $id;
        }
    }
    return null;
}