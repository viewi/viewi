<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Stripslashes extends BaseFunction
{
    public static string $name = 'stripslashes';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Stripslashes.js';
        return file_get_contents($jsToInclude);
    }
}
