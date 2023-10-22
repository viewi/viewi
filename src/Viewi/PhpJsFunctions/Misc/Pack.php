<?php

namespace Viewi\PhpJsFunctions\Misc;

use Viewi\JsTranspile\BaseFunction;

class Pack extends BaseFunction
{
    public static string $name = 'pack';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Pack.js';
        return file_get_contents($jsToInclude);
    }
}
