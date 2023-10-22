<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Strtotime extends BaseFunction
{
    public static string $name = 'strtotime';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtotime.js';
        return file_get_contents($jsToInclude);
    }
}
