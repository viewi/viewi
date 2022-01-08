<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class SubstrCount extends BaseFunctionConverter
{
    public static string $name = 'substr_count';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SubstrCount.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
