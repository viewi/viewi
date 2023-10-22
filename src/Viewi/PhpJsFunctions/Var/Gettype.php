<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Gettype extends BaseFunction
{
    public static string $name = 'gettype';

    public static function getUses(): array
    {
        return ['is_float'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Gettype.js';
        return file_get_contents($jsToInclude);
    }
}
