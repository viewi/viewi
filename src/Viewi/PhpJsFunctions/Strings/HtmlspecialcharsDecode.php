<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class HtmlspecialcharsDecode extends BaseFunction
{
    public static string $name = 'htmlspecialchars_decode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'HtmlspecialcharsDecode.js';
        return file_get_contents($jsToInclude);
    }
}
