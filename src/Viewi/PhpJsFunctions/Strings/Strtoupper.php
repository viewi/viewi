<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strtoupper extends BaseFunction
{
    public static string $name = 'strtoupper';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtoupper.js';
        return file_get_contents($jsToInclude);
    }
}
