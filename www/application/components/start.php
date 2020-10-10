<?php

use DevApp\DevRouter;

include 'routes.php';

$develop = true;
$renderReturn = true;
$upFolder = DIRECTORY_SEPARATOR . '..';
$buildRoot = __DIR__ . $upFolder . $upFolder . DIRECTORY_SEPARATOR;

Viewi\App::init(
    [
        'SOURCE_DIR' =>  __DIR__,
        'SERVER_BUILD_DIR' => $buildRoot . 'build',
        'PUBLIC_BUILD_DIR' => $buildRoot . 'public' . DIRECTORY_SEPARATOR . 'build',
        'DEV_MODE' => $develop,
        'RETURN_OUTPUT' => $renderReturn
    ]
);

// $response = $page->render(AppComponent::class);
