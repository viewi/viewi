<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Wordwrap extends BaseFunction
{
    public static string $name = 'wordwrap';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Wordwrap.js';
        return file_get_contents($jsToInclude);
    }
}
