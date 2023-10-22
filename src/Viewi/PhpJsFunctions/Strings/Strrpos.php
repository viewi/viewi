<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strrpos extends BaseFunction
{
    public static string $name = 'strrpos';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strrpos.js';
        return file_get_contents($jsToInclude);
    }
}
