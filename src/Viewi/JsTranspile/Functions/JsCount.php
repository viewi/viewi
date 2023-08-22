<?php

namespace Viewi\JsTranspile\Functions;

use Viewi\JsTranspile\BaseFunction;

class JsCount extends BaseFunction
{
    public static string $name = 'count';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'JsCount.js';
        return file_get_contents($jsToInclude);
    }
}
