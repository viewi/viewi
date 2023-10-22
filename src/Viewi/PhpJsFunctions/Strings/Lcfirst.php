<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Lcfirst extends BaseFunction
{
    public static string $name = 'lcfirst';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Lcfirst.js';
        return file_get_contents($jsToInclude);
    }
}
