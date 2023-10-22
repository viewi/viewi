<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Split extends BaseFunction
{
    public static string $name = 'split';

    public static function getUses(): array
    {
        return ['explode'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Split.js';
        return file_get_contents($jsToInclude);
    }
}
