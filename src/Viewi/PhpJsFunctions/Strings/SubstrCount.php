<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class SubstrCount extends BaseFunction
{
    public static string $name = 'substr_count';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SubstrCount.js';
        return file_get_contents($jsToInclude);
    }
}
