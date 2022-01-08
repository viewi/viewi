<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayUdiff extends BaseFunctionConverter
{
    public static string $name = 'array_udiff';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUdiff.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
