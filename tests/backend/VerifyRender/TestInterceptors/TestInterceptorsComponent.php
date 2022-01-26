<?php

namespace TestInterceptors;

use Viewi\BaseComponent;
use Viewi\Common\HttpClient;

class TestInterceptorsComponent extends BaseComponent
{
    public string $title = 'Posts -';
    public ?PostModel $post = null;

    public function __init(HttpClient $http, SessionInterceptor $session, AuthorizationInterceptor $auth)
    {
        $http
            ->with([$session, 'intercept'])
            ->with([$auth, 'intercept'])
            ->get('/api/posts/45')
            ->then(
                function (PostModel $post) {
                    $this->post = $post;
                },
                function ($error) {
                    echo $error;
                }
            );
    }
}
