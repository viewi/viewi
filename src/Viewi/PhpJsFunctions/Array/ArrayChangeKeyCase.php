<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayChangeKeyCase extends BaseFunction
{
    public static string $name = 'array_change_key_case';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayChangeKeyCase.js';
        return file_get_contents($jsToInclude);
    }
}
