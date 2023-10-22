<?php

namespace Viewi\PhpJsFunctions\Url;

use Viewi\JsTranspile\BaseFunction;

class Urlencode extends BaseFunction
{
    public static string $name = 'urlencode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Urlencode.js';
        return file_get_contents($jsToInclude);
    }
}
