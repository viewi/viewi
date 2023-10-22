<?php

namespace Viewi\PhpJsFunctions\Exec;

use Viewi\JsTranspile\BaseFunction;

class Escapeshellarg extends BaseFunction
{
    public static string $name = 'escapeshellarg';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Escapeshellarg.js';
        return file_get_contents($jsToInclude);
    }
}
