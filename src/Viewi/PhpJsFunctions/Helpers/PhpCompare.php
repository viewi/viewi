<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpCompare extends BaseFunction
{
    public static string $name = '_php_compare';

    public static function getUses(): array
    {
        return ['_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpCompare.js';
        return file_get_contents($jsToInclude);
    }
}
