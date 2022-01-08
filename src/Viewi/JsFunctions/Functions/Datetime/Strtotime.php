<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strtotime extends BaseFunctionConverter
{
    public static string $name = 'strtotime';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strtotime.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
