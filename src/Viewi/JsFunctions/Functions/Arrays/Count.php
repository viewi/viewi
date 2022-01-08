<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Count extends BaseFunctionConverter
{
    public static string $name = 'count';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Count.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
