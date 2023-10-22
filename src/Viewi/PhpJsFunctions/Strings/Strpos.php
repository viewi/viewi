<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strpos extends BaseFunction
{
    public static string $name = 'strpos';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strpos.js';
        return file_get_contents($jsToInclude);
    }
}
