<?php

namespace Viewi\Components\Http\Interceptor;

use Viewi\Components\Http\Message\Request;
use Viewi\Components\Http\Message\Response;

interface IHttpInterceptor
{
    function request(Request $request, IRequestHandler $handler);

    function response(Response $response, IResponseHandler $handler);
}
