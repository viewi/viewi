<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Getdate extends BaseFunctionConverter
{
    public static string $name = 'getdate';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Getdate.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
