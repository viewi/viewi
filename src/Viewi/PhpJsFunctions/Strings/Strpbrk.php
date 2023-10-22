<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strpbrk extends BaseFunction
{
    public static string $name = 'strpbrk';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strpbrk.js';
        return file_get_contents($jsToInclude);
    }
}
