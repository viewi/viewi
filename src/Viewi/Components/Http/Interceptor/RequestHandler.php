<?php

namespace Viewi\Components\Http\Interceptor;

use Viewi\App;
use Viewi\Builder\Attributes\Skip;
use Viewi\Components\Http\Message\Request;
use Viewi\Helpers;

#[Skip]
class RequestHandler implements IRequestHandler
{
    public int $current = -1;
    private int $interceptorsCount;
    private array $interceptorInstance = [];

    public function __construct(private $onHandle, private App $appInstance, private array $interceptors)
    {
        $this->interceptorsCount = count($this->interceptors);
    }

    function next(Request $request)
    {
        $this->current++;
        $this->run($request, true);
    }

    function reject(Request $request)
    {
        $this->current++;
        $this->run($request, false);
    }

    private function run(Request $request, bool $continue)
    {
        if ($this->current < $this->interceptorsCount) {
            $interceptorName = $this->interceptors[$this->current];
            $engine = $this->appInstance->engine();
            /**
             * @var IHttpInterceptor $interceptor
             */
            $interceptor = $engine->resolve($engine->shortName($interceptorName));
            $this->interceptorInstance[] = $interceptor;
            $interceptor->request($request, $this);
        } else {
            // Helpers::debug(['calling onHandle', $request]);
            ($this->onHandle)($request, $this->interceptorInstance);
        }
    }
}
