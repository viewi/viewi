<?php

declare(strict_types=1);

use App\Components\Views\Home\HomeComponent;
use Viewi\PageEngine;

require __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/app/components/views/home/Home.php';

$ds = DIRECTORY_SEPARATOR;

$engine = new PageEngine(
    __DIR__ . $ds . 'app' . $ds . 'components',
    __DIR__ . $ds . 'app' . $ds . 'build',
    __DIR__ . $ds . 'public' . $ds . 'build',
    true,
    true
);

echo $engine->render(HomeComponent::class);
