<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class VarExport extends BaseFunction
{
    public static string $name = 'var_export';

    public static function getUses(): array
    {
        return ['_php_array_entries', 'is_object', 'echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VarExport.js';
        return file_get_contents($jsToInclude);
    }
}
