<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Checkdate extends BaseFunctionConverter
{
    public static string $name = 'checkdate';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Checkdate.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
