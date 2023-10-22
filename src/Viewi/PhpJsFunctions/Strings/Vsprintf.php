<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Vsprintf extends BaseFunction
{
    public static string $name = 'vsprintf';

    public static function getUses(): array
    {
        return ['sprintf'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Vsprintf.js';
        return file_get_contents($jsToInclude);
    }
}
