<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Krsort extends BaseFunction
{
    public static string $name = 'krsort';

    public static function getUses(): array
    {
        return ['_php_sort'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Krsort.js';
        return file_get_contents($jsToInclude);
    }
}
