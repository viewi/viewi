<?php

use Viewi\JsTranspile\Functions\JsCount;
use Viewi\JsTranspile\Functions\Json\JsonEncode;
use Viewi\JsTranspile\Functions\Strings\Explode;
use Viewi\JsTranspile\Functions\Strings\Implode;
use Viewi\JsTranspile\Functions\Strings\Strlen;

return [
    'implode' => Implode::class,
    'explode' => Explode::class,
    'strlen' => Strlen::class,
    'count' => JsCount::class,
    'json_encode' => JsonEncode::class
];
