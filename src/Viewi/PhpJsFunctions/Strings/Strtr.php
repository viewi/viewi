<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strtr extends BaseFunction
{
    public static string $name = 'strtr';

    public static function getUses(): array
    {
        return ['krsort', 'ini_set'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtr.js';
        return file_get_contents($jsToInclude);
    }
}
