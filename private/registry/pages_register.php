<?php
/**
 * project-root/private/registry/pages_register.php
 *
 * Optional static pages registry (useful for seeding, routing tests,
 * or offline-mode when DB is unavailable).
 *
 * Fields:
 *  - id, subject_id, title, slug, nav_order, published(bool|int),
 *    meta_description, meta_keywords, icon(optional)
 *
 * Exposes: $PAGES_REGISTER, pages_all(), pages_by_subject($subjectId), page_by_slug($slug)
 */

if (defined('MK_REGISTRY_PAGES_LOADED')) { return; }
define('MK_REGISTRY_PAGES_LOADED', true);

$PAGES_REGISTER = [
    // Example stub:
    // [
    //   'id' => 1,
    //   'subject_id' => 1, // History
    //   'title' => 'Origins',
    //   'slug'  => 'origins',
    //   'nav_order' => 1,
    //   'published' => 1,
    //   'meta_description' => 'Origins of the community.',
    //   'meta_keywords' => 'origins, history, roots',
    //   'icon' => 'lib/images/icons/origins.svg',
    // ],
];

function pages_all(): array {
    global $PAGES_REGISTER;
    return $PAGES_REGISTER;
}

function pages_by_subject(int $subjectId): array {
    return array_values(array_filter(pages_all(), function($row) use ($subjectId) {
        return (int)($row['subject_id'] ?? 0) === $subjectId;
    }));
}

function page_by_slug(string $slug): ?array {
    foreach (pages_all() as $row) {
        if (($row['slug'] ?? '') === $slug) {
            return $row;
        }
    }
    return null;
}
