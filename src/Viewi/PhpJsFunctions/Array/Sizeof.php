<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Sizeof extends BaseFunction
{
    public static string $name = 'sizeof';

    public static function getUses(): array
    {
        return ['count'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sizeof.js';
        return file_get_contents($jsToInclude);
    }
}
