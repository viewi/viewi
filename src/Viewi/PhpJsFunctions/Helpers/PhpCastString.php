<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpCastString extends BaseFunction
{
    public static string $name = '_phpCastString';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpCastString.js';
        return file_get_contents($jsToInclude);
    }
}
