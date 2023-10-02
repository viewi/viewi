<?php

namespace Viewi\JsTranspile\Functions\Json;

use Viewi\JsTranspile\BaseFunction;

class JsonEncode extends BaseFunction
{
    public static string $name = 'json_encode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'JsonEncode.js';
        return file_get_contents($jsToInclude);
    }
}
