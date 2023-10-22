<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Md5 extends BaseFunction
{
    public static string $name = 'md5';

    public static function getUses(): array
    {
        return ['crypto', 'utf8_encode'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Md5.js';
        return file_get_contents($jsToInclude);
    }
}
