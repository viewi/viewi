<?php

namespace Viewi\PhpJsFunctions\Filesystem;

use Viewi\JsTranspile\BaseFunction;

class Basename extends BaseFunction
{
    public static string $name = 'basename';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Basename.js';
        return file_get_contents($jsToInclude);
    }
}
