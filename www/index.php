<?php

include 'core/PageEngine/PageEngine.php';
include 'application/pages/app.php';

$page = new PageEngine(
    __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'pages',
    AppComponent::class
);
$page->StartApp();
