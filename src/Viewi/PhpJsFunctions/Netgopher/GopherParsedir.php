<?php

namespace Viewi\PhpJsFunctions\Netgopher;

use Viewi\JsTranspile\BaseFunction;

class GopherParsedir extends BaseFunction
{
    public static string $name = 'gopher_parsedir';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'GopherParsedir.js';
        return file_get_contents($jsToInclude);
    }
}
