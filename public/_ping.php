<?php
// project-root/public/_ping.php

declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
echo "PING OK @ " . date('c') . "\n";
