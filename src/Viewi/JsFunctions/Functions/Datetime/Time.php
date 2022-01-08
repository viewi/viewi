<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Time extends BaseFunctionConverter
{
    public static string $name = 'time';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Time.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
