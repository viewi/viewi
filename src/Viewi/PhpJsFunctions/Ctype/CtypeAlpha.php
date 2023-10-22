<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypeAlpha extends BaseFunction
{
    public static string $name = 'ctype_alpha';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeAlpha.js';
        return file_get_contents($jsToInclude);
    }
}
