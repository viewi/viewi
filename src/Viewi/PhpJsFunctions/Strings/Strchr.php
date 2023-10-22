<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Strchr extends BaseFunction
{
    public static string $name = 'strchr';

    public static function getUses(): array
    {
        return ['strstr'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strchr.js';
        return file_get_contents($jsToInclude);
    }
}
