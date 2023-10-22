<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strtolower extends BaseFunction
{
    public static string $name = 'strtolower';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtolower.js';
        return file_get_contents($jsToInclude);
    }
}
