<?php

namespace Viewi\PhpJsFunctions\Funchand;

use Viewi\JsTranspile\BaseFunction;

class GetDefinedFunctions extends BaseFunction
{
    public static string $name = 'get_defined_functions';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'GetDefinedFunctions.js';
        return file_get_contents($jsToInclude);
    }
}
