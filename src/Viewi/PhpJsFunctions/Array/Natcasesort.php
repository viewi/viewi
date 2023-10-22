<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Natcasesort extends BaseFunction
{
    public static string $name = 'natcasesort';

    public static function getUses(): array
    {
        return ['strnatcasecmp'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Natcasesort.js';
        return file_get_contents($jsToInclude);
    }
}
