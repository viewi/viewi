<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Reset extends BaseFunction
{
    public static string $name = 'reset';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Reset.js';
        return file_get_contents($jsToInclude);
    }
}
