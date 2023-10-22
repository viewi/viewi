<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Ltrim extends BaseFunction
{
    public static string $name = 'ltrim';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ltrim.js';
        return file_get_contents($jsToInclude);
    }
}
