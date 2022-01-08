<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gmdate extends BaseFunctionConverter
{
    public static string $name = 'gmdate';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('date');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Gmdate.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
