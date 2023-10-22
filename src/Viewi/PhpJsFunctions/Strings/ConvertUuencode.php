<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class ConvertUuencode extends BaseFunction
{
    public static string $name = 'convert_uuencode';

    public static function getUses(): array
    {
        return ['is_scalar'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ConvertUuencode.js';
        return file_get_contents($jsToInclude);
    }
}
