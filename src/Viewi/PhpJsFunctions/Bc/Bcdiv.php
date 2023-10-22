<?php

namespace Viewi\PhpJsFunctions\Bc;

use Viewi\JsTranspile\BaseFunction;

class Bcdiv extends BaseFunction
{
    public static string $name = 'bcdiv';

    public static function getUses(): array
    {
        return ['_bc'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bcdiv.js';
        return file_get_contents($jsToInclude);
    }
}
