<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class SubstrCompare extends BaseFunctionConverter
{
    public static string $name = 'substr_compare';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SubstrCompare.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
