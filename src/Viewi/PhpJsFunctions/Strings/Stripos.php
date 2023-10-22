<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Stripos extends BaseFunction
{
    public static string $name = 'stripos';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Stripos.js';
        return file_get_contents($jsToInclude);
    }
}
