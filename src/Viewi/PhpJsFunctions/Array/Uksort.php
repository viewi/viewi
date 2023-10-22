<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Uksort extends BaseFunction
{
    public static string $name = 'uksort';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Uksort.js';
        return file_get_contents($jsToInclude);
    }
}
