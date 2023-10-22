<?php

namespace Viewi\PhpJsFunctions\Funchand;

use Viewi\JsTranspile\BaseFunction;

class FunctionExists extends BaseFunction
{
    public static string $name = 'function_exists';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'FunctionExists.js';
        return file_get_contents($jsToInclude);
    }
}
