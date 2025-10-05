<?php
// project-root/private/functions/page_functions.php

function find_all_pages() {
  global $db;
  $sql = "SELECT * FROM pages ORDER BY subject_id ASC, position ASC";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $rows = [];
  while($r = mysqli_fetch_assoc($result)) { $rows[] = $r; }
  mysqli_free_result($result);
  return $rows;
}

function count_pages_by_subject_id($subject_id) {
  global $db;
  $sql = "SELECT COUNT(*) AS c FROM pages WHERE subject_id='" . db_escape($db, $subject_id) . "'";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return (int)($row['c'] ?? 0);
}

function find_pages_by_subject_id($subject_id) {
  global $db;
  $sql = "SELECT * FROM pages WHERE subject_id='" . db_escape($db, $subject_id) . "' ORDER BY position ASC";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $rows = [];
  while($r = mysqli_fetch_assoc($result)) { $rows[] = $r; }
  mysqli_free_result($result);
  return $rows;
}

function find_pages_by_subject_id_paginated($subject_id, $limit, $offset) {
  global $db;
  $limit = (int)$limit; $offset = (int)$offset;
  $sql = "SELECT * FROM pages WHERE subject_id='" . db_escape($db, $subject_id) . "' ORDER BY position ASC LIMIT {$limit} OFFSET {$offset}";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $rows = [];
  while($r = mysqli_fetch_assoc($result)) { $rows[] = $r; }
  mysqli_free_result($result);
  return $rows;
}

function find_page_by_id($id) {
  global $db;
  $sql = "SELECT * FROM pages WHERE id='" . db_escape($db, $id) . "' LIMIT 1";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $row;
}

function find_page_by_slug($slug) {
  global $db;
  $sql = "SELECT * FROM pages WHERE slug='" . db_escape($db, $slug) . "' LIMIT 1";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $row = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $row;
}

function update_page_thumbnail($page_id, $filename) {
  global $db;
  $sql  = "UPDATE pages SET thumbnail='" . db_escape($db, $filename) . "' ";
  $sql .= "WHERE id='" . db_escape($db, $page_id) . "' LIMIT 1";
  $result = mysqli_query($db, $sql);
  return $result === true;
}

/**
 * Resolve a thumbnail URL for a page.
 * Priority:
 *  1) 'thumbnail' column in /uploads/pages/
 *  2) /lib/images/pages/{slug}.{ext}
 *  3) fallback /lib/images/pages/_placeholder.png
 */
function page_thumbnail_url(array $page) {
  $slug = $page['slug'] ?? '';
  if (!empty($page['thumbnail'])) {
    return url_for('/uploads/pages/' . $page['thumbnail']);
  }
  if ($slug !== '') {
    $candidates = ['png','jpg','jpeg','webp','gif','avif'];
    foreach ($candidates as $ext) {
      $rel = '/lib/images/pages/' . $slug . '.' . $ext;
      $abs = PUBLIC_PATH . str_replace('/', DIRECTORY_SEPARATOR, $rel);
      if (file_exists($abs)) return url_for($rel);
    }
  }
  return url_for('/lib/images/pages/_placeholder.png');
}

function delete_page_thumbnail($page_id) {
  global $db;

  $page = find_page_by_id($page_id);
  if(!$page) { return false; }

  $filename = $page['thumbnail'] ?? '';
  if($filename) {
    $abs = rtrim(UPLOADS_PAGES_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if(file_exists($abs)) { @unlink($abs); }
  }

  $sql  = "UPDATE pages SET thumbnail=NULL ";
  $sql .= "WHERE id='" . db_escape($db, $page_id) . "' LIMIT 1";
  $result = mysqli_query($db, $sql);
  return $result === true;
}

