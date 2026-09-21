<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Htmlentities extends BaseFunction
{
    public static string $name = 'htmlentities';

    public static function getUses(): array
    {
        return ['_php_html_escape'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Htmlentities.js';
        return file_get_contents($jsToInclude);
    }
}
