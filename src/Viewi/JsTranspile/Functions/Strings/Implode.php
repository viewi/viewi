<?php

namespace Viewi\JsTranspile\Functions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Implode extends BaseFunction
{
    public static string $name = 'implode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Implode.js';
        return file_get_contents($jsToInclude);
    }
}
