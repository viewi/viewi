<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class VarDump extends BaseFunction
{
    public static string $name = 'var_dump';

    public static function getUses(): array
    {
        return ['_phpCastString', '_php_array_entries', 'is_object', 'echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'VarDump.js';
        return file_get_contents($jsToInclude);
    }
}
