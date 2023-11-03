<?php

namespace Viewi\Components\Http\Interceptor;

use Viewi\Builder\Attributes\Skip;
use Viewi\Components\Http\Message\Response;
use Viewi\Helpers;

#[Skip]
class ResponseHandler implements IResponseHandler
{
    public int $current = -1;
    private int $interceptorsCount;

    public function __construct(private $onHandle, private array $interceptors)
    {
        $this->interceptorsCount = count($this->interceptors);
        $this->current = $this->interceptorsCount;
    }

    function next(Response $response)
    {
        $this->current--;
        $this->run($response, true);
    }

    function reject(Response $response)
    {
        $this->current--;
        $this->run($response, false);
    }

    private function run(Response $response, bool $continue)
    {
        if ($this->current > -1) {
            /**
             * @var IHttpInterceptor $interceptor
             */
            $interceptor = $this->interceptors[$this->current];
            $interceptor->response($response, $this);
        } else {
            // Helpers::debug(['calling onHandle', $response]);
            ($this->onHandle)($response);
        }
    }
}
