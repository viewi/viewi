<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Mktime extends BaseFunction
{
    public static string $name = 'mktime';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Mktime.js';
        return file_get_contents($jsToInclude);
    }
}
