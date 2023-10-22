<?php

namespace Viewi\PhpJsFunctions\Pcre;

use Viewi\JsTranspile\BaseFunction;

class PregQuote extends BaseFunction
{
    public static string $name = 'preg_quote';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PregQuote.js';
        return file_get_contents($jsToInclude);
    }
}
