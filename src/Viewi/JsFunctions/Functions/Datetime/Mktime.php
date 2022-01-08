<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Mktime extends BaseFunctionConverter
{
    public static string $name = 'mktime';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Mktime.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
