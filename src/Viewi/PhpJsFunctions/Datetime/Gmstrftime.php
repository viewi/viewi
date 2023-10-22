<?php

namespace Viewi\PhpJsFunctions\Datetime;

use Viewi\JsTranspile\BaseFunction;

class Gmstrftime extends BaseFunction
{
    public static string $name = 'gmstrftime';

    public static function getUses(): array
    {
        return ['strftime'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Gmstrftime.js';
        return file_get_contents($jsToInclude);
    }
}
