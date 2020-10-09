<?php

declare(strict_types=1);

// print_r($_SERVER['REDIRECT_URL']);

include 'core/Viewi/PageEngine.php';
include 'application/components/views/app.php';
include 'application/components/views/home/home.php';

$develop = true;
$renderReturn = false;

$page = new Viewi\PageEngine(
    __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'components',
    __DIR__ . DIRECTORY_SEPARATOR . 'build',
    __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'build',
    $develop,
    $renderReturn
);
//for ($i = 0; $i < 10; $i++) {
$response = $page->render(AppComponent::class);
echo $response;
//}

// testing 
// ob_start();
// for ($i = 0; $i < 1000; $i++) {
//     $page->render(AppComponent::class);
// }
// $html = ob_get_contents();
// ob_end_clean();


// echo '<pre>' . htmlentities($html) . '</pre>';
// echo $html;
// 
