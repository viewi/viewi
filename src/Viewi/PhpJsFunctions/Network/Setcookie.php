<?php

namespace Viewi\PhpJsFunctions\Network;

use Viewi\JsTranspile\BaseFunction;

class Setcookie extends BaseFunction
{
    public static string $name = 'setcookie';

    public static function getUses(): array
    {
        return ['setrawcookie'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Setcookie.js';
        return file_get_contents($jsToInclude);
    }
}
