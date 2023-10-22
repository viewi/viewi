<?php

namespace Viewi\PhpJsFunctions\Bc;

use Viewi\JsTranspile\BaseFunction;

class Bcadd extends BaseFunction
{
    public static string $name = 'bcadd';

    public static function getUses(): array
    {
        return ['_bc'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bcadd.js';
        return file_get_contents($jsToInclude);
    }
}
