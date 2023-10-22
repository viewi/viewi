<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strncmp extends BaseFunction
{
    public static string $name = 'strncmp';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strncmp.js';
        return file_get_contents($jsToInclude);
    }
}
