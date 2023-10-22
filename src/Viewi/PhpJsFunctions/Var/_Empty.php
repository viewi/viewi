<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class _Empty extends BaseFunction
{
    public static string $name = 'empty';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . '_Empty.js';
        return file_get_contents($jsToInclude);
    }
}
