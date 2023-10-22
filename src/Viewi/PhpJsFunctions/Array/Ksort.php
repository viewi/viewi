<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Ksort extends BaseFunction
{
    public static string $name = 'ksort';

    public static function getUses(): array
    {
        return ['i18n_loc_get_default', 'strnatcmp'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ksort.js';
        return file_get_contents($jsToInclude);
    }
}
