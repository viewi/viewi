<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypeXdigit extends BaseFunction
{
    public static string $name = 'ctype_xdigit';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeXdigit.js';
        return file_get_contents($jsToInclude);
    }
}
