<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Natsort extends BaseFunction
{
    public static string $name = 'natsort';

    public static function getUses(): array
    {
        return ['strnatcmp'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Natsort.js';
        return file_get_contents($jsToInclude);
    }
}
