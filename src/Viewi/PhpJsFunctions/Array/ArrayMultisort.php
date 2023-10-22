<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayMultisort extends BaseFunction
{
    public static string $name = 'array_multisort';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMultisort.js';
        return file_get_contents($jsToInclude);
    }
}
