<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Gmdate extends BaseFunction
{
    public static string $name = 'gmdate';

    public static function getUses(): array
    {
        return ['date'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Gmdate.js';
        return file_get_contents($jsToInclude);
    }
}
