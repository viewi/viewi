<?php

namespace TestHttpClient;

use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class TestHttpClientComponent extends BaseComponent
{
    public string $title = 'Posts -';
    public ?PostModel $post = null;

    public function __init(HttpClient $http)
    {
        $http->get('/api/posts/45')->then(
            function (PostModel $post) {
                $this->post = $post;
            },
            function ($error) {
                echo $error;
            }
        );
    }
}
