<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class VarDump extends BaseFunction
{
    public static string $name = 'var_dump';

    public static function getUses(): array
    {
        return ['echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VarDump.js';
        return file_get_contents($jsToInclude);
    }
}
