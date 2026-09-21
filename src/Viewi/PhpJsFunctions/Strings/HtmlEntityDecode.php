<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class HtmlEntityDecode extends BaseFunction
{
    public static string $name = 'html_entity_decode';

    public static function getUses(): array
    {
        return ['_php_html_unescape'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'HtmlEntityDecode.js';
        return file_get_contents($jsToInclude);
    }
}
