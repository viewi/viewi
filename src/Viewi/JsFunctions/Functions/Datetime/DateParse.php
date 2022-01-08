<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class DateParse extends BaseFunctionConverter
{
    public static string $name = 'date_parse';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('strtotime');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'DateParse.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
