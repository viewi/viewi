<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Htmlspecialchars extends BaseFunction
{
    public static string $name = 'htmlspecialchars';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Htmlspecialchars.js';
        return file_get_contents($jsToInclude);
    }
}
