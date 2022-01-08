<?php

namespace Viewi\JsFunctions\Functions\Url;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Urldecode extends BaseFunctionConverter
{
    public static string $name = 'urldecode';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Urldecode.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
