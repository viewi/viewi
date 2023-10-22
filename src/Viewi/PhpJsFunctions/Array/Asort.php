<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Asort extends BaseFunction
{
    public static string $name = 'asort';

    public static function getUses(): array
    {
        return ['strnatcmp', 'i18n_loc_get_default'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Asort.js';
        return file_get_contents($jsToInclude);
    }
}
