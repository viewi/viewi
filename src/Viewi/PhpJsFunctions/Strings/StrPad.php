<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrPad extends BaseFunction
{
    public static string $name = 'str_pad';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrPad.js';
        return file_get_contents($jsToInclude);
    }
}
