<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpArrayKey extends BaseFunction
{
    public static string $name = '_php_array_key';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpArrayKey.js';
        return file_get_contents($jsToInclude);
    }
}
