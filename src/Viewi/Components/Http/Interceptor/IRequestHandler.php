<?php

namespace Viewi\Components\Http\Interceptor;

use Viewi\Components\Http\Message\Request;

interface IRequestHandler
{
    function next(Request $request);

    function reject(Request $request);
}
