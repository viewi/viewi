<?php

namespace Viewi\PhpJsFunctions\Ctype;

use Viewi\JsTranspile\BaseFunction;

class CtypeSpace extends BaseFunction
{
    public static string $name = 'ctype_space';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeSpace.js';
        return file_get_contents($jsToInclude);
    }
}
