<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class MtGetrandmax extends BaseFunction
{
    public static string $name = 'mt_getrandmax';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'MtGetrandmax.js';
        return file_get_contents($jsToInclude);
    }
}
