<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Stristr extends BaseFunction
{
    public static string $name = 'stristr';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Stristr.js';
        return file_get_contents($jsToInclude);
    }
}
