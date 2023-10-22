<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strcmp extends BaseFunction
{
    public static string $name = 'strcmp';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strcmp.js';
        return file_get_contents($jsToInclude);
    }
}
