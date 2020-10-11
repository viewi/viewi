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
        $match = DevRouter::resolve($_SERVER['REDIRECT_URL']);
        if ($match === null) {
            throw new Exception('No route was matched!');
        }
        // print_r($match);
        $action = $match['route']['action'];
        $response = '';
        if (is_callable($action)) {
            $response = $action(...array_values($match['params']));
        } else {
            $instance = new $action();
            $response = $instance();
        }
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

ViewiRoute::addAdapter(new ViewiRouteAdapter());

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
