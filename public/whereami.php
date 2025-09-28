<?php
// project-root/public/whereami.php

header('Content-Type: text/plain; charset=utf-8');
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . PHP_EOL;
echo "__FILE__: " . __FILE__ . PHP_EOL;
echo "PWD: " . getcwd() . PHP_EOL;
