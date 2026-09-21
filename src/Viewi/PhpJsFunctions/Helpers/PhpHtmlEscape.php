<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpHtmlEscape extends BaseFunction
{
    public static string $name = '_php_html_escape';

    public static function getUses(): array
    {
        return ['_php_html_entities', '_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpHtmlEscape.js';
        return file_get_contents($jsToInclude);
    }
}
