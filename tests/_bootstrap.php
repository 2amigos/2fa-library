<?php
// Search for autoload, since performance is irrelevant and usability isn't!
$dir = __DIR__ . '/..';
while (!file_exists($dir . '/vendor/autoload.php')) {
    if ($dir == dirname($dir)) {
        throw new \Exception('Failed to locate autoload.php');
    }
    $dir = dirname($dir);
}

$vendor = $dir . '/vendor';

require_once $vendor . '/autoload.php';
