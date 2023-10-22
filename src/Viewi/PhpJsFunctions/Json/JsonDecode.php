<?php

namespace Viewi\PhpJsFunctions\Json;

use Viewi\JsTranspile\BaseFunction;

class JsonDecode extends BaseFunction
{
    public static string $name = 'json_decode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'JsonDecode.js';
        return file_get_contents($jsToInclude);
    }
}
