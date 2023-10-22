<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Gettimeofday extends BaseFunction
{
    public static string $name = 'gettimeofday';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Gettimeofday.js';
        return file_get_contents($jsToInclude);
    }
}
