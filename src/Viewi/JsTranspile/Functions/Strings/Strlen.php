<?php

namespace Viewi\JsTranspile\Functions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strlen extends BaseFunction
{
    public static string $name = 'strlen';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strlen.js';
        return file_get_contents($jsToInclude);
    }
}
