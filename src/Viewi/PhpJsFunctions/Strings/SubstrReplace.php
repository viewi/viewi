<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class SubstrReplace extends BaseFunction
{
    public static string $name = 'substr_replace';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SubstrReplace.js';
        return file_get_contents($jsToInclude);
    }
}
