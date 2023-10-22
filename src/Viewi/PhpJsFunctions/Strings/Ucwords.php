<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Ucwords extends BaseFunction
{
    public static string $name = 'ucwords';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ucwords.js';
        return file_get_contents($jsToInclude);
    }
}
