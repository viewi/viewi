<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Sscanf extends BaseFunction
{
    public static string $name = 'sscanf';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sscanf.js';
        return file_get_contents($jsToInclude);
    }
}
