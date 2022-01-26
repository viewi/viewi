<?php

namespace TestMiddleware;

use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class TestMiddlewareComponent extends BaseComponent
{
    public static array $_beforeStart = [AuthGuard::class, SessionGuard::class];

    public string $title = 'Posts -';
    public ?PostModel $post = null;

    public function __init(HttpClient $http)
    {
        $http
            ->get('/api/posts/45')
            ->then(
                function (PostModel $post) {
                    $this->post = $post;
                },
                function ($error) {
                    $this->title = $error;
                }
            );
    }
}
