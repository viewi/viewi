<?php

namespace Viewi\PhpJsFunctions\Info;

use Viewi\JsTranspile\BaseFunction;

class IniGet extends BaseFunction
{
    public static string $name = 'ini_get';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IniGet.js';
        return file_get_contents($jsToInclude);
    }
}
