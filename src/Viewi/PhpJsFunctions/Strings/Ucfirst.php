<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Ucfirst extends BaseFunction
{
    public static string $name = 'ucfirst';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ucfirst.js';
        return file_get_contents($jsToInclude);
    }
}
