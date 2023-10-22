<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Strval extends BaseFunction
{
    public static string $name = 'strval';

    public static function getUses(): array
    {
        return ['gettype'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strval.js';
        return file_get_contents($jsToInclude);
    }
}
