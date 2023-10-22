<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strnatcmp extends BaseFunction
{
    public static string $name = 'strnatcmp';

    public static function getUses(): array
    {
        return ['_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strnatcmp.js';
        return file_get_contents($jsToInclude);
    }
}
