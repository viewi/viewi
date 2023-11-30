<?php

use Viewi\App;

$config = require  __DIR__ . '/config.php';
$publicConfig = require  __DIR__ . '/publicConfig.php';

$app = new App($config, $publicConfig);

return $app;