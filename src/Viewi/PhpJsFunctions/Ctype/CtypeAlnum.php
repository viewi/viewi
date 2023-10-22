<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypeAlnum extends BaseFunction
{
    public static string $name = 'ctype_alnum';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeAlnum.js';
        return file_get_contents($jsToInclude);
    }
}
