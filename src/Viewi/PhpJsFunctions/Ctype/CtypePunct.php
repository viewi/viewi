<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypePunct extends BaseFunction
{
    public static string $name = 'ctype_punct';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypePunct.js';
        return file_get_contents($jsToInclude);
    }
}
