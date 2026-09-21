<?php

namespace Viewi\PhpJsFunctions\Pcre;

use Viewi\JsTranspile\BaseFunction;

class PregReplace extends BaseFunction
{
    public static string $name = 'preg_replace';

    public static function getUses(): array
    {
        return ['_php_regex', '_phpCastString', '_php_cast_int', '_php_array', '_php_array_entries'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PregReplace.js';
        return file_get_contents($jsToInclude);
    }
}
