<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Decoct extends BaseFunction
{
    public static string $name = 'decoct';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Decoct.js';
        return file_get_contents($jsToInclude);
    }
}
