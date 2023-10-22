<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class PrintR extends BaseFunction
{
    public static string $name = 'print_r';

    public static function getUses(): array
    {
        return ['echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PrintR.js';
        return file_get_contents($jsToInclude);
    }
}
