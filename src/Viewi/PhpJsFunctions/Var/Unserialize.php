<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Unserialize extends BaseFunction
{
    public static string $name = 'unserialize';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Unserialize.js';
        return file_get_contents($jsToInclude);
    }
}
