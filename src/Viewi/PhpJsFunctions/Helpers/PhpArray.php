<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpArray extends BaseFunction
{
    public static string $name = '_php_array';

    public static function getUses(): array
    {
        return ['_php_array_key'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpArray.js';
        return file_get_contents($jsToInclude);
    }
}
