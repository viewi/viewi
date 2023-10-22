<?php

namespace Viewi\PhpJsFunctions\Info;

use Viewi\JsTranspile\BaseFunction;

class IniSet extends BaseFunction
{
    public static string $name = 'ini_set';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IniSet.js';
        return file_get_contents($jsToInclude);
    }
}
