<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Idate extends BaseFunctionConverter
{
    public static string $name = 'idate';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Idate.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
