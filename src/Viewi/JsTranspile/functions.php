<?php

use Viewi\PhpJsFunctions\JsCount;
use Viewi\PhpJsFunctions\Json\JsonEncode;
use Viewi\PhpJsFunctions\Strings\Explode;
use Viewi\PhpJsFunctions\Strings\Implode;
use Viewi\PhpJsFunctions\Strings\Strlen;

return [
    'implode' => Implode::class,
    'explode' => Explode::class,
    'strlen' => Strlen::class,
    'count' => JsCount::class,
    'json_encode' => JsonEncode::class
];
