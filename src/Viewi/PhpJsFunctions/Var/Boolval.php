<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Boolval extends BaseFunction
{
    public static string $name = 'boolval';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Boolval.js';
        return file_get_contents($jsToInclude);
    }
}
