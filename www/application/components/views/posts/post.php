<?php

use DevApp\PostModel;
use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class PostPage extends BaseComponent
{
    public ?PostModel $post = null;
    public string $title = 'Post';

    function __init(int $postId, HttpClient $http)
    {
        $http->get("/api/posts/$postId")->then(
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
