<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strptime extends BaseFunctionConverter
{
    public static string $name = 'strptime';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $translator->includeFunction('array_map');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strptime.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
