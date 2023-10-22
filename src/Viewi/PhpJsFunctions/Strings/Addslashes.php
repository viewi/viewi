<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Addslashes extends BaseFunction
{
    public static string $name = 'addslashes';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Addslashes.js';
        return file_get_contents($jsToInclude);
    }
}
