<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpSetOp extends BaseFunction
{
    public static string $name = '_php_set_op';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array', '_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpSetOp.js';
        return file_get_contents($jsToInclude);
    }
}
