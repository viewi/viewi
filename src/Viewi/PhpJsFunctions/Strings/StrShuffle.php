<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrShuffle extends BaseFunction
{
    public static string $name = 'str_shuffle';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrShuffle.js';
        return file_get_contents($jsToInclude);
    }
}
