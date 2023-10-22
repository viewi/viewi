<?php

namespace Viewi\PhpJsFunctions\Url;

use Viewi\JsTranspile\BaseFunction;

class Rawurlencode extends BaseFunction
{
    public static string $name = 'rawurlencode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rawurlencode.js';
        return file_get_contents($jsToInclude);
    }
}
