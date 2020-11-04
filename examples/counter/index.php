<?php

declare(strict_types=1);

use App\Components\Views\Home\HomeComponent;
use Viewi\App;
use Viewi\PageEngine;

require __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/app/components/views/home/Home.php';

$ds = DIRECTORY_SEPARATOR;

App::init([
    PageEngine::SOURCE_DIR => __DIR__ . '/app/components',
    PageEngine::SERVER_BUILD_DIR => __DIR__ . '/app/build',
    PageEngine::PUBLIC_BUILD_DIR => __DIR__ . '/public/build',
    PageEngine::DEV_MODE => true,
    PageEngine::RETURN_OUTPUT => true
]);
$engine = App::getEngine();

echo $engine->render(HomeComponent::class);
