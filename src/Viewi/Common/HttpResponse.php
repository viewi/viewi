<?php

namespace Viewi\Common;

class HttpResponse
{
    public $content = null;
    public int $status = 0;
    public string $statusText = "Not Started";
    public array $headers = [];
    public bool $success = true;
    public bool $canceled = false;
}
