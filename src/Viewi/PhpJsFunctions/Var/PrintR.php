<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class PrintR extends BaseFunction
{
    public static string $name = 'print_r';

    public static function getUses(): array
    {
        return ['_phpCastString', '_php_array_entries', 'is_object', 'echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PrintR.js';
        return file_get_contents($jsToInclude);
    }
}
