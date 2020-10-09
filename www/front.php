<?php

declare(strict_types=1);

include 'core/Viewi/PageEngine.php';
include 'application/components/views/home/home.php';

$develop = true;
$renderReturn = true;

$page = new Viewi\PageEngine(
    __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'components',
    __DIR__ . DIRECTORY_SEPARATOR . 'build',
    __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'build',
    $develop,
    $renderReturn
);

$response = $page->render(HomePage::class);
echo $response;
