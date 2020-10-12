<?php

namespace DevApp;

use Exception;
use Viewi\Routing\Route as ViewiRoute;
use ViewiRouteAdapter;

include 'DevRouter.php';
include 'PostModel.php';
include 'core/Viewi/App.php';
include 'ViewiRouterAdapter.php';

/**
 * 
 * Simple application for development purposes
 */
class DevApp
{
    public function run()
    {
        $response = DevRouter::handle($_SERVER['REDIRECT_URL'], $_SERVER['REQUEST_METHOD']);
        if (is_string($response)) { // text/html
            header("Content-type: text/html; charset=utf-8");
            echo $response;
        } else { // json
            header("Content-type: application/json; charset=utf-8");
            echo json_encode($response);
        }
    }
}

$app = new DevApp();

ViewiRoute::setAdapter(new ViewiRouteAdapter());

DevRouter::register('get', '/api/posts/{postId}', function ($postId) {
    $post = new PostModel();
    $post->id = $postId;
    $post->name = 'Amazing Viewi';
    $post->content = 'Get ready for a new development experience!';
    $post->date = date("Y-m-d H:i:s");
    return $post;
});

include 'application/components/start.php';

return $app;
