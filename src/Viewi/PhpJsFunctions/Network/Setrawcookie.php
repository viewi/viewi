<?php

namespace Viewi\PhpJsFunctions\Network;

use Viewi\JsTranspile\BaseFunction;

class Setrawcookie extends BaseFunction
{
    public static string $name = 'setrawcookie';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Setrawcookie.js';
        return file_get_contents($jsToInclude);
    }
}
