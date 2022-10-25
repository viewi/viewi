<?php

declare(strict_types=1);

use App\Components\Views\Home\HomeComponent;
use Viewi\App;
use Viewi\AppInit;

require __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/app/components/views/home/Home.php';

$config = AppInit::create()
    ->setSourceDir(__DIR__ . '/app/components')
    ->setServerBuildDir(__DIR__ . '/app/build')
    ->setPublicBuildDir(__DIR__ . '/public/build')
    ->setPublicRootDir(__DIR__ . '/public')
    ->setOutputMode()
    ->setMode();

$engine = App::initEngine($config);

echo $engine->render(HomeComponent::class);
