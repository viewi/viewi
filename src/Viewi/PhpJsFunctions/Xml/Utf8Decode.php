<?php

namespace Viewi\PhpJsFunctions\Xml;

use Viewi\JsTranspile\BaseFunction;

class Utf8Decode extends BaseFunction
{
    public static string $name = 'utf8_decode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Utf8Decode.js';
        return file_get_contents($jsToInclude);
    }
}
