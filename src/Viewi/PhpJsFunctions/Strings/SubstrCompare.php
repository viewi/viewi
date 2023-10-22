<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class SubstrCompare extends BaseFunction
{
    public static string $name = 'substr_compare';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SubstrCompare.js';
        return file_get_contents($jsToInclude);
    }
}
