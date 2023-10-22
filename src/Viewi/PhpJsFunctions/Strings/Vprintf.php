<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Vprintf extends BaseFunction
{
    public static string $name = 'vprintf';

    public static function getUses(): array
    {
        return ['sprintf', 'echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Vprintf.js';
        return file_get_contents($jsToInclude);
    }
}
