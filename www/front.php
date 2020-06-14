<?php

declare(strict_types=1);

include 'core/PageEngine/PageEngine.php';
include 'application/components/views/home/home.php';

$develop = true;
$renderReturn = true;

$page = new Vo\PageEngine(
    __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'components',
    __DIR__ . DIRECTORY_SEPARATOR . 'build',
    __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'build',
    $develop,
    $renderReturn
);

$response = $page->render(HomePage::class);
echo $response;
