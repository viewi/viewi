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

// testing 
ob_start();
$page->render();
$html = ob_get_contents();
ob_end_clean();
echo '<pre>' . htmlentities($html) . '</pre>';
echo $html;
?>
<style>
    html,
    body {
        background-color: #E9E9E9;
        color: #000;
    }
</style>