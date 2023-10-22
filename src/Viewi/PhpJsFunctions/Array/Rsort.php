<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Rsort extends BaseFunction
{
    public static string $name = 'rsort';

    public static function getUses(): array
    {
        return ['i18n_loc_get_default', 'strnatcmp'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rsort.js';
        return file_get_contents($jsToInclude);
    }
}
