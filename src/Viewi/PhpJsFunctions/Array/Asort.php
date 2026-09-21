<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Asort extends BaseFunction
{
    public static string $name = 'asort';

    public static function getUses(): array
    {
        return ['_php_sort'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Asort.js';
        return file_get_contents($jsToInclude);
    }
}
