<?php

namespace Viewi\PhpJsFunctions\Funchand;

use Viewi\JsTranspile\BaseFunction;

class CreateFunction extends BaseFunction
{
    public static string $name = 'create_function';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CreateFunction.js';
        return file_get_contents($jsToInclude);
    }
}
