<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypeUpper extends BaseFunction
{
    public static string $name = 'ctype_upper';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeUpper.js';
        return file_get_contents($jsToInclude);
    }
}
