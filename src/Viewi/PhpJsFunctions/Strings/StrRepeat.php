<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrRepeat extends BaseFunction
{
    public static string $name = 'str_repeat';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrRepeat.js';
        return file_get_contents($jsToInclude);
    }
}
