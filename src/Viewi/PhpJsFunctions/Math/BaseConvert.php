<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class BaseConvert extends BaseFunction
{
    public static string $name = 'base_convert';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'BaseConvert.js';
        return file_get_contents($jsToInclude);
    }
}
