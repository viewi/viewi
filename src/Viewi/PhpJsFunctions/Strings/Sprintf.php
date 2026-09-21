<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Sprintf extends BaseFunction
{
    public static string $name = 'sprintf';

    public static function getUses(): array
    {
        return ['_phpCastString', '_php_cast_int', '_php_cast_float'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sprintf.js';
        return file_get_contents($jsToInclude);
    }
}
