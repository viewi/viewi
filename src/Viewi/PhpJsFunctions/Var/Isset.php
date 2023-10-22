<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Isset extends BaseFunction
{
    public static string $name = 'isset';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Isset.js';
        return file_get_contents($jsToInclude);
    }
}
