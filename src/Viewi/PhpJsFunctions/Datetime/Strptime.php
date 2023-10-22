<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Strptime extends BaseFunction
{
    public static string $name = 'strptime';

    public static function getUses(): array
    {
        return ['setlocale', 'array_map'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strptime.js';
        return file_get_contents($jsToInclude);
    }
}
