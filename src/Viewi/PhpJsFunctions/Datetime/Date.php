<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Date extends BaseFunction
{
    public static string $name = 'date';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Date.js';
        return file_get_contents($jsToInclude);
    }
}
