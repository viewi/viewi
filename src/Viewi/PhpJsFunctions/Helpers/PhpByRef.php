<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpByRef extends BaseFunction
{
    public static string $name = '_php_by_ref';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpByRef.js';
        return file_get_contents($jsToInclude);
    }
}
