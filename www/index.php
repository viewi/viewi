<?php
declare(strict_types=1);

include 'core/PageEngine/PageEngine.php';
include 'application/pages/app.php';

$page = new PageEngine(
    __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'pages',
    __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'build',
    AppComponent::class
);
$page->startApp();

?>
<style>
    html, body{
        background-color: #E9E9E9;
        color:#000;
    }
</style>