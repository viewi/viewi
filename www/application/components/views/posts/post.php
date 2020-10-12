<?php

use DevApp\PostModel;
use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class PostPage extends BaseComponent
{
    public ?PostModel $post = null;
    public string $title = 'Post';

    function __init(HttpClient $http)
    {
        $http->get('/api/posts/45')->then(
            function (PostModel $post) {
                $this->post = $post;
                // print_r($this);
            },
            function ($error) {
                // print_r($error);
            }
        );
    }
}
