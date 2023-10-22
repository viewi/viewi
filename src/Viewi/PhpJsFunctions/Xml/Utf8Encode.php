<?php

namespace Viewi\PhpJsFunctions\Xml;

use Viewi\JsTranspile\BaseFunction;

class Utf8Encode extends BaseFunction
{
    public static string $name = 'utf8_encode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Utf8Encode.js';
        return file_get_contents($jsToInclude);
    }
}
