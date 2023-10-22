<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strrev extends BaseFunction
{
    public static string $name = 'strrev';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strrev.js';
        return file_get_contents($jsToInclude);
    }
}
