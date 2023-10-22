<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strcspn extends BaseFunction
{
    public static string $name = 'strcspn';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strcspn.js';
        return file_get_contents($jsToInclude);
    }
}
