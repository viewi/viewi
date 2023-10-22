<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Doubleval extends BaseFunction
{
    public static string $name = 'doubleval';

    public static function getUses(): array
    {
        return ['floatval'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Doubleval.js';
        return file_get_contents($jsToInclude);
    }
}
