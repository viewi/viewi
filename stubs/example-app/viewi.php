<?php

use Viewi\App;

$config = require 'config.php';
include __DIR__ . '/routes.php';
App::init($config);
