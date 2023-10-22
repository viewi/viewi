<?php

namespace Viewi\PhpJsFunctions\Filesystem;

use Viewi\JsTranspile\BaseFunction;

class Realpath extends BaseFunction
{
    public static string $name = 'realpath';

    public static function getUses(): array
    {
        return ['path'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Realpath.js';
        return file_get_contents($jsToInclude);
    }
}
