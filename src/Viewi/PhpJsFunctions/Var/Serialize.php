<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Serialize extends BaseFunction
{
    public static string $name = 'serialize';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Serialize.js';
        return file_get_contents($jsToInclude);
    }
}
