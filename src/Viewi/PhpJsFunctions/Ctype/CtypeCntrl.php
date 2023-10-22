<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypeCntrl extends BaseFunction
{
    public static string $name = 'ctype_cntrl';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeCntrl.js';
        return file_get_contents($jsToInclude);
    }
}
