<?php

namespace Viewi\PhpJsFunctions\Url;

use Viewi\JsTranspile\BaseFunction;

class Base64Decode extends BaseFunction
{
    public static string $name = 'base64_decode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Base64Decode.js';
        return file_get_contents($jsToInclude);
    }
}
