<?php
// project-root/private/shared/subjects/public_subjects_header.php

// $body_class can be set by the page before including this file
$body_class = $body_class ?? 'public-subjects';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Mkomigbo – Subjects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- main subjects css -->
    <link rel="stylesheet" href="<?= url_for('/lib/css/subjects.css'); ?>">
  </head>
  <body class="<?= h($body_class); ?>">
    <header class="subjects-header">
      <div class="site-header">
        <div class="site-title">
          Mkomigbo Subjects
        </div>
        <nav class="main-nav">
          <!-- Public home for subjects -->
          <a href="<?= url_for('/subjects/'); ?>">Home</a>
          <!-- You can point this to / (public root) if you like -->
          <a href="<?= url_for('/'); ?>">Mkomigbo</a>
          <!-- Optional: public platforms or archives -->
          <a href="<?= url_for('/subjects/?view=all'); ?>">All Subjects</a>
          <!-- Keep staff out of here -->
          <a href="<?= url_for('/staff/login.php'); ?>">Staff Login</a>
        </nav>
      </div>
    </header>
