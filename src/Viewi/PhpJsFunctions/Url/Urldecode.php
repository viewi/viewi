<?php

namespace Viewi\PhpJsFunctions\Url;

use Viewi\JsTranspile\BaseFunction;

class Urldecode extends BaseFunction
{
    public static string $name = 'urldecode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Urldecode.js';
        return file_get_contents($jsToInclude);
    }
}
