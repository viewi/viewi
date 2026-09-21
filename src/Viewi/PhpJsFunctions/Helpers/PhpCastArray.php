<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpCastArray extends BaseFunction
{
    public static string $name = '_php_cast_array';

    public static function getUses(): array
    {
        return ['is_object'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpCastArray.js';
        return file_get_contents($jsToInclude);
    }
}
