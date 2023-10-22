<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsObject extends BaseFunction
{
    public static string $name = 'is_object';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsObject.js';
        return file_get_contents($jsToInclude);
    }
}
