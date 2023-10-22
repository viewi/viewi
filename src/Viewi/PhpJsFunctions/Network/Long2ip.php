<?php

namespace Viewi\PhpJsFunctions\Network;

use Viewi\JsTranspile\BaseFunction;

class Long2ip extends BaseFunction
{
    public static string $name = 'long2ip';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Long2ip.js';
        return file_get_contents($jsToInclude);
    }
}
