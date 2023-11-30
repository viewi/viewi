<?php

require __DIR__ . '/../vendor/autoload.php';

echo 'Building Viewi' . PHP_EOL;

/**
 * @var Viewi\App
 */
$app = include __DIR__ . '/viewi.php';
include __DIR__ . '/routes.php';
$logs = $app->build();
echo $logs;
