<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Crc32 extends BaseFunction
{
    public static string $name = 'crc32';

    public static function getUses(): array
    {
        return ['utf8_encode'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Crc32.js';
        return file_get_contents($jsToInclude);
    }
}
