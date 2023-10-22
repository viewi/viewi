<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsCallable extends BaseFunction
{
    public static string $name = 'is_callable';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsCallable.js';
        return file_get_contents($jsToInclude);
    }
}
