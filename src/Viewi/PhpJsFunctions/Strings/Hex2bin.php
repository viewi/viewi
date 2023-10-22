<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Hex2bin extends BaseFunction
{
    public static string $name = 'hex2bin';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Hex2bin.js';
        return file_get_contents($jsToInclude);
    }
}
