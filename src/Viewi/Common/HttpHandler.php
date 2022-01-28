<?php

namespace Viewi\Common;

class HttpHandler
{
    public ?HttpClient $httpClient = null;
    public ?HttpResponse $response = null;
    public ?HttpHandler $previousHandler = null;
    public bool $top = false;
    public $onHandle;
    public $after = null;
    public $onReject;
    public bool $continue = false;

    public function handle(callable $after)
    {
        // echo ' ||handle|| ';
        $this->after = $after;
        ($this->onHandle)();
    }

    public function reject($error)
    {
        ($this->onReject)($error);
    }
}
