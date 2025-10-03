<?php

use _NS_\Views\Home\HomePage;
use _NS_\Views\NotFound\NotFoundPage;
use Viewi\App;
use Viewi\Components\Http\Message\Response;

/**
 * @var App $app
 */
$router = $app->router();
$router->get('/', HomePage::class);
$router
    ->get('*', NotFoundPage::class)
    ->transform(function (Response $response) {
        return $response->withStatus(404, 'Not Found');
    });
