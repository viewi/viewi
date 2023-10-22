<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Fmod extends BaseFunction
{
    public static string $name = 'fmod';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Fmod.js';
        return file_get_contents($jsToInclude);
    }
}
