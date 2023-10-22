<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Sort extends BaseFunction
{
    public static string $name = 'sort';

    public static function getUses(): array
    {
        return ['i18n_loc_get_default'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sort.js';
        return file_get_contents($jsToInclude);
    }
}
