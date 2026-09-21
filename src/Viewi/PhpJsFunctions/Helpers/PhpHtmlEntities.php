<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpHtmlEntities extends BaseFunction
{
    public static string $name = '_php_html_entities';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpHtmlEntities.js';
        return file_get_contents($jsToInclude);
    }
}
