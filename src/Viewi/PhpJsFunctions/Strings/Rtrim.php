<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Rtrim extends BaseFunction
{
    public static string $name = 'rtrim';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rtrim.js';
        return file_get_contents($jsToInclude);
    }
}
