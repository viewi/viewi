<?php

namespace Viewi\Common;

use DevApp\PostModel;

class HttpClient
{
    public function get($url)
    {
        $resolver = new PromiseResolver(function () use ($url) {
            // call router internally and get result
        });

        return $resolver;
    }
}
