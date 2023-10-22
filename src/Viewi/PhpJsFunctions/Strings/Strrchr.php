<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strrchr extends BaseFunction
{
    public static string $name = 'strrchr';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strrchr.js';
        return file_get_contents($jsToInclude);
    }
}
