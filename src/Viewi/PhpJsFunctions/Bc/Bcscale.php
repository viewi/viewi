<?php

namespace Viewi\PhpJsFunctions\Bc;

use Viewi\JsTranspile\BaseFunction;

class Bcscale extends BaseFunction
{
    public static string $name = 'bcscale';

    public static function getUses(): array
    {
        return ['_bc'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bcscale.js';
        return file_get_contents($jsToInclude);
    }
}
