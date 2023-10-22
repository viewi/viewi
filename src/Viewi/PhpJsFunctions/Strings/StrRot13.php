<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrRot13 extends BaseFunction
{
    public static string $name = 'str_rot13';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrRot13.js';
        return file_get_contents($jsToInclude);
    }
}
