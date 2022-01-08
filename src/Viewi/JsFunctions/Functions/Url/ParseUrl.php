<?php

namespace Viewi\JsFunctions\Functions\Url;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ParseUrl extends BaseFunctionConverter
{
    public static string $name = 'parse_url';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ParseUrl.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
