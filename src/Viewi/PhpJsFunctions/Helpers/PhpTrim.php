<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpTrim extends BaseFunction
{
    public static string $name = '_php_trim';

    public static function getUses(): array
    {
        return ['_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpTrim.js';
        return file_get_contents($jsToInclude);
    }
}
