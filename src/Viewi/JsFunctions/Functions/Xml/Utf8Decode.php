<?php

namespace Viewi\JsFunctions\Functions\Xml;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Utf8Decode extends BaseFunctionConverter
{
    public static string $name = 'utf8_decode';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Utf8Decode.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
