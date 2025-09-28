<?php
// project-root/public/subjects/subject.php

require_once(__DIR__ . '/../../private/assets/initialize.php');

// Accept either ?slug= or ?id=
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$id   = isset($_GET['id'])   ? (int)$_GET['id']   : 0;

if ($slug === '' && $id > 0) {
  // Resolve slug by ID
  $stmt = $db->prepare("SELECT slug FROM subjects WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  if ($res && !empty($res['slug'])) {
    $slug = $res['slug'];
  }
}

if ($slug !== '') {
  // Always redirect to canonical pretty URL /{slug}/
  header("Location: /" . rawurlencode($slug) . "/", true, 301);
  exit;
}

// Fallback: no params → go to subjects list
header("Location: /subjects/", true, 302);
exit;
