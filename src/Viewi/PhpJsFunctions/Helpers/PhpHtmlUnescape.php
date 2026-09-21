<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpHtmlUnescape extends BaseFunction
{
    public static string $name = '_php_html_unescape';

    public static function getUses(): array
    {
        return ['_php_html_entities', '_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpHtmlUnescape.js';
        return file_get_contents($jsToInclude);
    }
}
