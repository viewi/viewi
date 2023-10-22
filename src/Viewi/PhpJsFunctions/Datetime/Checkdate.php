<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Checkdate extends BaseFunction
{
    public static string $name = 'checkdate';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Checkdate.js';
        return file_get_contents($jsToInclude);
    }
}
