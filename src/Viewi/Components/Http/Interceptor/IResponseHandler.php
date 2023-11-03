<?php

namespace Viewi\Components\Http\Interceptor;

use Viewi\Components\Http\Message\Response;

interface IResponseHandler
{
    function next(Response $request);

    function reject(Response $request);
}
