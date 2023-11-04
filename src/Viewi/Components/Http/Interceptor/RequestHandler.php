<?php

namespace Viewi\Components\Http\Interceptor;

use Viewi\App;
use Viewi\Builder\Attributes\Skip;
use Viewi\Components\Http\Message\Request;
use Viewi\Engine;
use Viewi\Helpers;

#[Skip]
class RequestHandler implements IRequestHandler
{
    public int $current = -1;
    private int $interceptorsCount;
    private array $interceptorInstance = [];

    public function __construct(private $onHandle, private Engine $engine, private array $interceptors)
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
        if (!$continue) {
            ($this->onHandle)($request, $this->interceptorInstance, $continue);
            return;
        }
        if ($this->current < $this->interceptorsCount) {
            $interceptorName = $this->interceptors[$this->current];
            /**
             * @var IHttpInterceptor $interceptor
             */
            $interceptor = $this->engine->resolve($this->engine->shortName($interceptorName));
            $this->interceptorInstance[] = $interceptor;
            $interceptor->request($request, $this);
        } else {
            // Helpers::debug(['calling onHandle', $request]);
            ($this->onHandle)($request, $this->interceptorInstance, $continue);
        }
    }
}
