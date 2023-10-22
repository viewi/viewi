<?php

namespace Viewi\PhpJsFunctions\Network;

use Viewi\JsTranspile\BaseFunction;

class InetPton extends BaseFunction
{
    public static string $name = 'inet_pton';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'InetPton.js';
        return file_get_contents($jsToInclude);
    }
}
