<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpArraySet extends BaseFunction
{
    public static string $name = '_php_array_set';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpArraySet.js';
        return file_get_contents($jsToInclude);
    }
}
