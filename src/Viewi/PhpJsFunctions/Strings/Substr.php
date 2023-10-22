<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Substr extends BaseFunction
{
    public static string $name = 'substr';

    public static function getUses(): array
    {
        return ['_phpCastString', 'ini_get'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Substr.js';
        return file_get_contents($jsToInclude);
    }
}
