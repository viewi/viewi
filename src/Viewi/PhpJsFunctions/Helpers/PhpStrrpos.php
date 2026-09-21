<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpStrrpos extends BaseFunction
{
    public static string $name = '_php_strrpos';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpStrrpos.js';
        return file_get_contents($jsToInclude);
    }
}
