<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class MtRand extends BaseFunction
{
    public static string $name = 'mt_rand';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'MtRand.js';
        return file_get_contents($jsToInclude);
    }
}
