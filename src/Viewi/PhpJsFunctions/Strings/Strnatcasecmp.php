<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strnatcasecmp extends BaseFunction
{
    public static string $name = 'strnatcasecmp';

    public static function getUses(): array
    {
        return ['strnatcmp', '_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strnatcasecmp.js';
        return file_get_contents($jsToInclude);
    }
}
