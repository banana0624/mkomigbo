<?php
// private/functions/subject_helpers.php

/**
 * Convert a DB slug to a CSS-safe class.
 * Example: "language-2" => "language2"
 */
function subject_css_class_from_slug(string $slug): string {
    $slug = strtolower($slug);
    // remove everything that is not a-z or 0-9
    $slug = preg_replace('/[^a-z0-9]+/', '', $slug);
    return $slug;
}

/**
 * Build the body class string for subject pages.
 * - $subject can be null (e.g. subjects listing)
 * - $is_public = true => 'public-subjects'
 */
function body_classes_for_subject(?array $subject = null, bool $is_public = true): string {
    $classes = [];

    // context class
    $classes[] = $is_public ? 'public-subjects' : 'staff-subjects';

    // subject-specific classes
    if ($subject && !empty($subject['slug'])) {
        $classes[] = 'subject';
        $classes[] = subject_css_class_from_slug($subject['slug']);
    }

    return implode(' ', $classes);
}
