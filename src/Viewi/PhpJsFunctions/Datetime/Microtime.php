<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Microtime extends BaseFunction
{
    public static string $name = 'microtime';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Microtime.js';
        return file_get_contents($jsToInclude);
    }
}
