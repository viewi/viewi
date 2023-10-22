<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Shuffle extends BaseFunction
{
    public static string $name = 'shuffle';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Shuffle.js';
        return file_get_contents($jsToInclude);
    }
}
