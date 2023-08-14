<?php

namespace Viewi\JsTranspile\Functions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Explode extends BaseFunction
{
    public static string $name = 'explode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Explode.js';
        return file_get_contents($jsToInclude);
    }
}
